<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Observer;

use Akeneo\Connector\Executor\JobExecutor;
use Akeneo\Connector\Helper\Import\Entities;
use Akeneo\Connector\Helper\Output;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FillDefaultStoreValues implements ObserverInterface
{
    public const CONFIG_PATH = 'akeneo_connector/justbetter/fill_default_store_values';

    public const DEFAULT_LANGUAGE_PATH = 'akeneo_connector/justbetter/defaultlanguage';

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected ResourceConnection $resourceConnection,
        protected Entities $entitiesHelper,
        protected Output $outputHelper,
        protected StoreHelper $storeHelper
    ) {}

    public function execute(Observer $observer): void
    {
        if (! $this->scopeConfig->isSetFlag(self::CONFIG_PATH)) {
            return;
        }

        /** @var JobExecutor $executor */
        $executor = $observer->getData('import');

        if ($executor->getMethod() !== 'dropTable') {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $tmpTable = $this->entitiesHelper->getTableName($executor->getCurrentJob()->getCode());

        if (! $connection->isTableExists($tmpTable)) {
            return;
        }

        $sourceStoreIds = $this->getSourceStoreIds();

        if ($sourceStoreIds === []) {
            $executor->displayInfo(
                $this->outputHelper->getPrefix().__('Skipping default store EAV fill: "Default language for admin channel" is not configured or does not match any store.')
            );

            return;
        }

        $executor->displayInfo(
            $this->outputHelper->getPrefix().__('Fill default store EAV values for required attributes')
        );

        foreach ($this->getRequiredAttributes($connection) as $attribute) {
            if ($attribute['backend_type'] === 'static') {
                continue;
            }

            $this->fillForAttribute(
                $connection,
                (int) $attribute['attribute_id'],
                (string) $attribute['backend_type'],
                $tmpTable,
                $sourceStoreIds
            );
        }
    }

    /**
     * @return array<int, int>
     */
    protected function getSourceStoreIds(): array
    {
        $defaultLanguage = (string) $this->scopeConfig->getValue(self::DEFAULT_LANGUAGE_PATH);

        if ($defaultLanguage === '') {
            return [];
        }

        /** @var array<string, array<int, array{store_id: int|string}>> $storesByLang */
        $storesByLang = $this->storeHelper->getStores('lang');

        if (! isset($storesByLang[$defaultLanguage])) {
            return [];
        }

        $storeIds = [];
        foreach ($storesByLang[$defaultLanguage] as $store) {
            $storeId = (int) $store['store_id'];
            if ($storeId !== 0) {
                $storeIds[$storeId] = $storeId;
            }
        }

        return array_values($storeIds);
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function getRequiredAttributes(AdapterInterface $connection): array
    {
        $select = $connection->select()
            ->from(
                ['attr' => $connection->getTableName('eav_attribute')],
                ['attribute_id', 'attribute_code', 'backend_type']
            )
            ->join(
                ['type' => $connection->getTableName('eav_entity_type')],
                'attr.entity_type_id = type.entity_type_id',
                []
            )
            ->where('type.entity_type_code = ?', 'catalog_product')
            ->where('attr.is_required = ?', 1);

        return $connection->fetchAll($select);
    }

    /**
     * @param  array<int, int>  $sourceStoreIds
     */
    protected function fillForAttribute(
        AdapterInterface $connection,
        int $attributeId,
        string $backendType,
        string $tmpTable,
        array $sourceStoreIds
    ): void {
        $valueTable = $connection->getTableName('catalog_product_entity_'.$backendType);
        $sourceStoreList = implode(',', array_map('intval', $sourceStoreIds));

        $sql = sprintf(
            'UPDATE %1$s v0
             INNER JOIN (
                 SELECT entity_id, MIN(store_id) AS source_store_id
                 FROM %1$s
                 WHERE attribute_id = %2$d
                   AND store_id IN (%4$s)
                   AND %5$s
                 GROUP BY entity_id
             ) src ON src.entity_id = v0.entity_id
             INNER JOIN %1$s v1
                 ON v1.entity_id = src.entity_id
                AND v1.attribute_id = %2$d
                AND v1.store_id = src.source_store_id
             INNER JOIN %3$s t ON t._entity_id = v0.entity_id
             SET v0.value = v1.value
             WHERE v0.attribute_id = %2$d
               AND v0.store_id = 0
               AND %6$s',
            $connection->quoteIdentifier($valueTable),
            $attributeId,
            $connection->quoteIdentifier($tmpTable),
            $sourceStoreList,
            'NOT '.$this->getEmptyCheck($backendType, 'value'),
            $this->getEmptyCheck($backendType, 'v0.value')
        );

        $connection->query($sql);
    }

    protected function getEmptyCheck(string $backendType, string $column): string
    {
        if ($backendType === 'varchar' || $backendType === 'text') {
            return sprintf("(%1\$s IS NULL OR %1\$s = '')", $column);
        }

        return sprintf('%s IS NULL', $column);
    }
}

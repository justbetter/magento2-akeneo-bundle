<?php
declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Plugin\Helper\Import;

use Akeneo\Connector\Helper\Import\Attribute as AttributeHelper;
use Akeneo\Connector\Helper\Import\Entities as EntitiesHelper;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\ScopeInterface;

class Entities
{
    public function __construct(
        protected ScopeConfigInterface $config,
        protected StoreHelper $storeHelper,
        protected AttributeHelper $attributeHelper
    ) {
    }

    public function afterFormatMediaName(EntitiesHelper $subject, mixed $result): mixed
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/formatmedianame', ScopeInterface::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return $result;
        }

        return str_replace('_', '-', $result);
    }

    /**
     * Before setting the values we use Tax Type value from Akeneo when available
     *
     * @param array<string, mixed> $data
     * @return array{0: string, 1: string, 2: array<string, mixed>, 3: int, 4: int, 5: int}
     */
    public function beforeSetValues(
        EntitiesHelper $subject,
        string $jobCode,
        string $entityTable,
        array $data,
        int $entityTypeId,
        int $storeId,
        int $mode = AdapterInterface::INSERT_ON_DUPLICATE
    ): array {
        $additionalTypes = $this->attributeHelper->getAdditionalTypes();

        foreach ($additionalTypes as $key => $additionalType) {
            if ($additionalType === 'tax' && isset($data[$key])) {
                if (isset($data['tax_class_id']) && $data['tax_class_id'] instanceof \Zend_Db_Expr) {
                    $defaultTaxClassId = $data['tax_class_id']->__toString();
                    $data['tax_class_id'] = new \Zend_Db_Expr(
                        "IF(`{$data[$key]}` IS NULL OR `{$data[$key]}` = '', '{$defaultTaxClassId}', `{$data[$key]}`)"
                    );
                } else {
                    $data['tax_class_id'] = new \Zend_Db_Expr(
                        "IF(`{$data[$key]}` IS NULL OR `{$data[$key]}` = '', `_tax_class_id`, `{$data[$key]}`)"
                    );
                }
            }
        }

        return [$jobCode, $entityTable, $data, $entityTypeId, $storeId, $mode];
    }
}

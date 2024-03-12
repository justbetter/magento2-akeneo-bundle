<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Akeneo\Connector\Job\Product;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Statement\Pdo\Mysql;
use Magento\Framework\Serialize\SerializerInterface;
use Zend_Db_Statement_Exception;

class CheckWebsiteAssociation
{
    public function __construct(
        protected ProductImportHelper $entitiesHelper,
        protected StoreHelper $storeHelper,
        protected ScopeConfigInterface $config,
        protected ConfigHelper $configHelper,
        protected Authenticator $authenticator,
        protected SerializerInterface $serializer
    ) {
    }

    /**
     * @throws Zend_Db_Statement_Exception
     * @throws Exception
     */
    public function beforeSetWebsites(Product $subject): array
    {
        $connection = $this->entitiesHelper->getConnection();
        $tmpTable = $this->entitiesHelper->getTableName($subject->getCode());
        $websiteAttribute = $this->configHelper->getWebsiteAttribute();
        $websiteAssociation = $this->config->getValue('akeneo_connector/product/website_attribute');

        $requiredAttributes = $this->getRequiredAttributes();

        if ($connection->tableColumnExists($tmpTable, $websiteAttribute)) {
            $select = $connection->select()->from(
                $tmpTable
            );
            /** @var Mysql $query */
            $query = $connection->query($select);
            /** @var array $row */
            while (($row = $query->fetch())) {
                
                if(!isset($row[$websiteAssociation])) {
                    continue;
                }

                $websites = explode(',', (string) $row[$websiteAssociation]);
                $mapping = $this->getMappedWebsiteChannels();

                foreach ($websites as $key => $website) {
                    $channel = $mapping[$website] ?? '';
                    if (empty($channel)) {
                        continue;
                    }

                    $locales = $this->storeHelper->getChannelStoreLangs($channel);
                    foreach ($requiredAttributes as $attribute) {
                        if (isset($attribute['localizable']) && $attribute['localizable'] === true) {
                            foreach ($locales as $locale) {
                                if (empty($row[$attribute['akeneo_attribute'] . '-' . $locale . '-' . $channel])) {
                                    unset($websites[$key]);
                                    break(2);
                                }
                            }
                        } else {
                            if (empty($row[$attribute])) {
                                unset($websites[$key]);
                                break(2);
                            }
                        }
                    }
                }

                $connection->update(
                    $tmpTable,
                    [
                        $websiteAssociation => implode(',', $websites),
                    ],
                    ['identifier = ?' => $row['identifier']]
                );
            }
        }

        return [$subject];
    }

    /**
     * @throws Exception
     */
    public function getMappedWebsiteChannels(): array
    {
        $mapping = $this->configHelper->getWebsiteMapping();
        /** @var string[] $channels */
        $channels = array_column($mapping, 'channel', 'website');

        return $channels;
    }

    /**
     * @throws Exception
     */
    public function getRequiredAttributes(): float|array|bool|int|string|null
    {
        if (!($requiredAttributes = $this->config->getValue('akeneo_connector/product/required_attribute_mapping'))) {
            return [];
        }
        $requiredAttributes = $this->serializer->unserialize($requiredAttributes);

        foreach ($requiredAttributes as $key => &$requiredAttribute) {
            $akeneoAttribute = $this->authenticator->getAkeneoApiClient()->getAttributeApi()->get($requiredAttribute['akeneo_attribute']);
            $requiredAttribute['localizable'] = $akeneoAttribute['localizable'];
        }

        return $requiredAttributes;
    }
}

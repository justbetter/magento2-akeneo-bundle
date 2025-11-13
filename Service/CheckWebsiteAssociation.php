<?php

namespace JustBetter\AkeneoBundle\Service;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Statement\Pdo\Mysql;
use Magento\Framework\Serialize\SerializerInterface;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Authenticator;

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

    public function execute(string $code): void
    {
        $connection = $this->entitiesHelper->getConnection();
        $tmpTable = $this->entitiesHelper->getTableName($code);
        $websiteAttribute = $this->configHelper->getWebsiteAttribute();
        $websites = $this->storeHelper->getStores('website_code');
        $websiteAssociation = $this->config->getValue('akeneo_connector/product/website_attribute');

        $requiredAttributes = $this->getRequiredAttributes();

        if (!$connection->tableColumnExists($tmpTable, $websiteAttribute)) {
            return;
        }

        $select = $connection->select()->from($tmpTable);
        $query = $connection->query($select);

        while (($row = $query->fetch())) {
            if (!isset($row[$websiteAssociation])) {
                continue;
            }

            $websites = explode(',', $row[$websiteAssociation]);
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
                                break 2;
                            }
                        }
                    } else {
                        if (empty($row[$attribute])) {
                            unset($websites[$key]);
                            break 2;
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

    protected function getMappedWebsiteChannels(): array
    {
        $mapping = $this->configHelper->getWebsiteMapping();
        return array_column($mapping, 'channel', 'website');
    }

    protected function getRequiredAttributes(): array
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

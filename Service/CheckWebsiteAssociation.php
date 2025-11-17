<?php

/**
 * JustBetter Magento2 Akeneo Bundle
 *
 * @author JustBetter B.V.
 * @copyright Copyright (c) JustBetter B.V. (https://justbetter.nl)
 * @package Magento2 Akeneo Bundle
 *
 * Licensed under the GNU General Public License v3.0 or later.
 * For full license information, see the LICENSE file
 * or visit <https://github.com/justbetter/magento2-akeneo-bundle/blob/master/LICENSE>.
 */


declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Service;

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

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

        while (($row = $query->fetch()) && is_array($row)) {
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
                    if (!is_array($attribute)) {
                        continue;
                    }
                    if (isset($attribute['localizable']) && $attribute['localizable'] === true) {
                        foreach ($locales as $locale) {
                            if (empty($row[$attribute['akeneo_attribute'] . '-' . $locale . '-' . $channel])) {
                                unset($websites[$key]);

                                break 2;
                            }
                        }
                    } else {
                        $attrKey = $attribute['akeneo_attribute'] ?? '';
                        if (empty($row[$attrKey])) {
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

    /**
     * @return array<string, string>
     */
    protected function getMappedWebsiteChannels(): array
    {
        $mapping = $this->configHelper->getWebsiteMapping();

        return array_column($mapping, 'channel', 'website');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getRequiredAttributes(): array
    {
        if (!($requiredAttributes = $this->config->getValue('akeneo_connector/product/required_attribute_mapping'))) {
            return [];
        }
        $unserialized = $this->serializer->unserialize($requiredAttributes);
        
        if (!is_array($unserialized)) {
            return [];
        }

        foreach ($unserialized as $key => &$requiredAttribute) {
            if (!is_array($requiredAttribute)) {
                continue;
            }
            $akeneoAttribute = $this->authenticator->getAkeneoApiClient()->getAttributeApi()->get($requiredAttribute['akeneo_attribute']);
            $requiredAttribute['localizable'] = $akeneoAttribute['localizable'];
        }

        return $unserialized;
    }
}

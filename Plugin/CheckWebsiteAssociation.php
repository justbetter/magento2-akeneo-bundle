<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Pim\ApiClient\Search\SearchBuilderFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Statement\Pdo\Mysql;
use Magento\Framework\Serialize\SerializerInterface;
use Zend_Db_Expr as expression;
use Akeneo\Connector\Job\Product;
use Magento\Store\Model\ScopeInterface as scope;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Authenticator;

class CheckWebsiteAssociation
{
    protected $entitiesHelper;
    protected $storeHelper;
    protected $config;
    protected $serializer;

    /**
     * construct function
     * @param ProductImportHelper $entitiesHelper
     */
    public function __construct(
        ProductImportHelper $entitiesHelper,
        StoreHelper $storeHelper,
        ScopeConfigInterface $config,
        ConfigHelper $configHelper,
        Authenticator $authenticator,
        SearchBuilderFactory $searchBuilderFactory,
        SerializerInterface $serializer
    ) {
        $this->entitiesHelper = $entitiesHelper;
        $this->storeHelper = $storeHelper;
        $this->config = $config;
        $this->configHelper = $configHelper;
        $this->authenticator = $authenticator;
        $this->searchBuilderFactory = $searchBuilderFactory;
        $this->serializer = $serializer;
    }

    public function beforeSetWebsites(product $subject)
    {
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tmpTable */
        $tmpTable = $this->entitiesHelper->getTableName($subject->getCode());
        $websiteAttribute = $this->configHelper->getWebsiteAttribute();
        $websites = $this->storeHelper->getStores('website_code');
        $websiteAssociation = $this->config->getValue('akeneo_connector/product/website_attribute');

        $requiredAttributes = $this->getRequiredAttributes();

        if ($connection->tableColumnExists($tmpTable, $websiteAttribute)) {
            /** @var Select $select */
            $select = $connection->select()->from(
                $tmpTable
            );
            /** @var Mysql $query */
            $query = $connection->query($select);
            /** @var array $row */
            while (($row = $query->fetch())) {

                $mapping = $this->getMappedWebsiteChannels();

                $websites = explode(',', $row[$websiteAssociation]);

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

    public function getMappedWebsiteChannels()
    {
        /** @var mixed[] $mapping */
        $mapping = $this->configHelper->getWebsiteMapping();
        /** @var string[] $channels */
        $channels = array_column($mapping, 'channel', 'website');

        return $channels;
    }

    public function getRequiredAttributes()
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

<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Magento\Store\Model\ScopeInterface as scope;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Akeneo\Connector\Helper\Import\Entities;
use Akeneo\Connector\Helper\Store as StoreHelper;

class CategoryExist
{
    protected $config;
    protected $entitiesHelper;
    protected $storeHelper;

    public function __construct(
        ScopeConfigInterface $config,
        Entities $entitiesHelper,
        StoreHelper $storeHelper
    ) {
        $this->config = $config;
        $this->entitiesHelper = $entitiesHelper;
        $this->storeHelper = $storeHelper;
    }

    public function beforeSetValues()
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/categoryexist', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return ;
        }

        $connection = $this->entitiesHelper->getConnection();

        $stores = $this->storeHelper->getStores('lang');
        foreach ($stores as $local => $affected) {
            foreach ($affected as $store) {
                $updateUrl = [
                    'url_key-' . $store['lang'] => null
                ];

                $connection->update(
                    'tmp_akeneo_connector_entities_category',
                    $updateUrl,
                    '_is_new = 0'
                );
            }
        }
    }
}

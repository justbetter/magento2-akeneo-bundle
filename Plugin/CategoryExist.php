<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Helper\Import\Entities;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as Scope;

class CategoryExist
{
    public function __construct(
        protected ScopeConfigInterface $config,
        protected Entities $entitiesHelper,
        protected StoreHelper $storeHelper
    ) {
    }

    public function beforeSetValues(): void
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/categoryexist', Scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return;
        }

        $connection = $this->entitiesHelper->getConnection();

        $stores = $this->storeHelper->getStores('lang');
        foreach ($stores as $affected) {
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

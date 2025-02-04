<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Job\Product;
use Akeneo\Connector\Helper\Import\Entities;
use Magento\Store\Model\ScopeInterface as Scope;
use Magento\Framework\App\Config\ScopeConfigInterface;

class InsertNewProducts
{
    public function __construct(
        protected ScopeConfigInterface $config,
        protected Entities $entitiesHelper
    ) {
    }

    public function afterInsertData(Product $subject)
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/insertnewproducts', Scope::SCOPE_WEBSITE);
        if ($extensionEnabled) {            
            $connection = $this->entitiesHelper->getConnection();
            $tmpTableName = $this->entitiesHelper->getTableName($subject->getCode());
            
            $connection->delete($tmpTableName, ['_is_new = ?' => 1]);
        }
    }
}

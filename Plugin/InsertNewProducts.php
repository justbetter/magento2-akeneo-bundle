<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Job\Product;
use Akeneo\Connector\Helper\Import\Entities;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as scope;

class InsertNewProducts
{
    public function __construct(
        protected ScopeConfigInterface $config,
        protected Entities $entitiesHelper
    ) {
    }

    public function afterInsertData(Product $subject, bool $result): bool
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/insertnewproducts', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return $result;
        }

        $connection = $this->entitiesHelper->getConnection();
        $tmpTableName = $this->entitiesHelper->getTableName($subject->getCode());

        $connection->delete($tmpTableName, ['_is_new = ?' => 1]);

        return $result;
    }
}

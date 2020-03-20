<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Magento\Store\Model\ScopeInterface as scope;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Akeneo\Connector\Helper\Import\Entities;
use Akeneo\Connector\Job\Product;

class InsertNewProducts
{
    protected $config;
    protected $entitiesHelper;

    /**
     * __construct function
     * @param ScopeConfigInterface $config
     * @param Entities $entitiesHelper
     */
    public function __construct(
        ScopeConfigInterface $config,
        Entities $entitiesHelper
    ) {
        $this->config = $config;
        $this->entitiesHelper = $entitiesHelper;
    }

    /**
     * afterInsertData function
     * @param  product $subject
     * @param  bool $result
     * @return bool $result
     */
    public function afterInsertData(product $subject, $result)
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

<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Job\Product;
use Akeneo\Connector\Helper\Import\Entities;
use Magento\Store\Model\ScopeInterface as scope;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class SetProductsActive
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
     * @param  product $product
     * @param  bool $result
     * @return bool $result
     */
    public function afterInsertData(product $product, $result)
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/setproductsactive', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return $result;
        }

        //todo: Save enabled status on product
//        $product->setStatus(Status::STATUS_ENABLED);
//        $product->save();

        return $result;
    }
}

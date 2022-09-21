<?php

namespace JustBetter\AkeneoBundle\Plugin\Helper\Import;

use Magento\Store\Model\ScopeInterface as scope;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Entities
{
    protected $config;
    protected $storeHelper;

    public function __construct(
        ScopeConfigInterface $config,
        StoreHelper $storeHelper
    ) {
        $this->config = $config;
        $this->storeHelper = $storeHelper;
    }

    public function afterFormatMediaName($subject, $result)
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/formatmedianame', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return $result;
        }

        return str_replace("_", "-", $result);
    }
}

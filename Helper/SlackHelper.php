<?php

namespace JustBetter\AkeneoBundle\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class SlackHelper extends AbstractHelper
{
    const XML_PATH = 'akeneo_connector/justbetter/slack/';

    /**
     * getConfigValue function
     * @param  String $field
     * @param  Int $storeId
     */
    public function getConfigValue($field, int|string|null $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * getGeneralConfig function
     * @param  String $code
     * @param  null $storeId
     */
    public function getGeneralConfig($code, int|string|null $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH . $code, $storeId);
    }

    /**
     * isEnable function
     * @return boolean
     */
    public function isEnable()
    {
        return $this->getGeneralConfig('enable');
    }
}

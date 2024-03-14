<?php

namespace JustBetter\AkeneoBundle\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class SlackHelper extends AbstractHelper
{
    public const XML_PATH = 'akeneo_connector/justbetter/slack/';

    public function getConfigValue(string $field, ?int $storeId = null): mixed
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getGeneralConfig(string $code, ?int $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH . $code, $storeId);
    }

    public function isEnable(): bool
    {
        return $this->getGeneralConfig('enable');
    }
}

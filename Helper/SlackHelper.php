<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

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

    public function getGeneralConfig(string $code, ?int $storeId = null): mixed
    {
        return $this->getConfigValue(self::XML_PATH . $code, $storeId);
    }

    public function isEnable(): bool
    {
        return (bool)$this->getGeneralConfig('enable');
    }
}

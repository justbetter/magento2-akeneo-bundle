<?php
/**
 * JustBetter Magento2 Akeneo Bundle
 *
 * @author JustBetter B.V.
 * @copyright Copyright (c) JustBetter B.V. (https://justbetter.nl)
 * @package Magento2 Akeneo Bundle
 *
 * Licensed under the GNU General Public License v3.0 or later.
 * For full license information, see the LICENSE file
 * or visit <https://github.com/justbetter/magento2-akeneo-bundle/blob/master/LICENSE>.
 */

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

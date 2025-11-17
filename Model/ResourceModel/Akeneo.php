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

namespace JustBetter\AkeneoBundle\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Akeneo extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('akeneo_connector_entities', 'id');
    }
}

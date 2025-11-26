<?php
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

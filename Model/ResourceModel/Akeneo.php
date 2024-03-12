<?php

namespace JustBetter\AkeneoBundle\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Akeneo extends AbstractDb
{
    /**
     * Initialize resource model
     */
    protected function _construct(): void
    {
        $this->_init('akeneo_connector_entities', 'id');
    }
}

<?php

namespace JustBetter\AkeneoBundle\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Akeneo extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('akeneo_connector_entities', 'id');
    }
}

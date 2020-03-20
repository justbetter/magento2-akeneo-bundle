<?php

namespace JustBetter\AkeneoBundle\Model\ResourceModel\Akeneo;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('JustBetter\AkeneoBundle\Model\Akeneo', 'JustBetter\AkeneoBundle\Model\ResourceModel\Akeneo');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}

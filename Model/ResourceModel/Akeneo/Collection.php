<?php

namespace JustBetter\AkeneoBundle\Model\ResourceModel\Akeneo;

use JustBetter\AkeneoBundle\Model\Akeneo;
use JustBetter\AkeneoBundle\Model\ResourceModel\Akeneo as AkeneoResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(Akeneo::class, AkeneoResourceModel::class);
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}

<?php

namespace JustBetter\AkeneoBundle\Model\ResourceModel\Akeneo;

use JustBetter\AkeneoBundle\Model\Akeneo;
use JustBetter\AkeneoBundle\Model\ResourceModel\Akeneo as AkeneoResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     */
    protected function _construct(): void
    {
        $this->_init(Akeneo::class, AkeneoResource::class);
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}

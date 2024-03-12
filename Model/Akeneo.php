<?php

namespace JustBetter\AkeneoBundle\Model;

use Magento\Framework\Model\AbstractModel;

class Akeneo extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Akeneo::class);
    }
}

<?php

namespace JustBetter\AkeneoBundle\Model;

class Akeneo extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('JustBetter\AkeneoBundle\Model\ResourceModel\Akeneo');
    }
}

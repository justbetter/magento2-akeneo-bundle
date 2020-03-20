<?php
namespace JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('akeneo_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Akeneo Information'));
    }
}

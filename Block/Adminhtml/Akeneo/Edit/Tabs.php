<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('akeneo_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Akeneo Information'));
    }
}

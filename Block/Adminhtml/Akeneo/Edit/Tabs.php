<?php

/**
 * JustBetter Magento2 Akeneo Bundle
 *
 * @author JustBetter B.V.
 * @copyright Copyright (c) JustBetter B.V. (https://justbetter.nl)
 * @package Magento2 Akeneo Bundle
 *
 * Licensed under the GNU General Public License v3.0 or later.
 * For full license information, see the LICENSE file
 * or visit <https://github.com/justbetter/magento2-akeneo-bundle/blob/master/LICENSE>.
 */


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

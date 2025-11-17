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

namespace JustBetter\AkeneoBundle\Block\Adminhtml;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;

class Akeneo extends Container
{
    protected $_template = 'akeneo/akeneo.phtml';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _prepareLayout(): Akeneo
    {
        $addButtonProps = [
            'id' => 'add_new',
            'label' => __('Add New'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->getAddButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock( // @phpstan-ignore-line
                'JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Grid',
                'justbetter.akeneo.grid'
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getAddButtonOptions(): array
    {
        return [
            [
                'label' => __('Add New'),
                'onclick' => "setLocation('" . $this->getCreateUrl() . "')"
            ]
        ];
    }

    protected function getCreateUrl(): string
    {
        return $this->getUrl('akeneomanager/*/new');
    }

    public function getGridHtml(): string
    {
        return $this->getChildHtml('grid');
    }
}

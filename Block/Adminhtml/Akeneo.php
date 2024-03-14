<?php

namespace JustBetter\AkeneoBundle\Block\Adminhtml;

use JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Grid;
use Magento\Backend\Block\Widget\Button\SplitButton;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;

class Akeneo extends Container
{
    /**
     * @var string
     */
    protected $_template = 'akeneo/akeneo.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     */
    protected function _prepareLayout(): Akeneo
    {
        $addButtonProps = [
            'id' => 'add_new',
            'label' => __('Add New'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => SplitButton::class,
            'options' => $this->_getAddButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);
        

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(Grid::class, 'justbetter.akeneo.grid')
        );
        return parent::_prepareLayout();
    }

    protected function _getAddButtonOptions(): array
    {
        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];

        return $splitButtonOptions;
    }

    protected function _getCreateUrl(): string
    {
        return $this->getUrl(
            'akeneomanager/*/new'
        );
    }

    public function getGridHtml(): string
    {
        return $this->getChildHtml('grid');
    }
}

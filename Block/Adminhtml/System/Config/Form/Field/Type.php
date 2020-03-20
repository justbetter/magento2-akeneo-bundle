<?php

namespace JustBetter\AkeneoBundle\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Akeneo\Connector\Helper\Import\Attribute as AttributeHelper;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Type class
 */
class Type extends AbstractFieldArray
{
    protected $elementFactory;
    protected $attributeHelper;
    protected $customerGroup;

    /**
     * construct function
     *
     * @param Context $context
     * @param ElementFactory $elementFactory
     * @param AttributeHelper $attributeHelper
     * @param Collection $customerGroup
     * @param array $data
     */
    public function __construct(
        Context $context,
        ElementFactory $elementFactory,
        AttributeHelper $attributeHelper,
        Collection $customerGroup,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->attributeHelper = $attributeHelper;
        $this->elementFactory  = $elementFactory;
        $this->customerGroup = $customerGroup;
    }

    /**
     * construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn('pim_type', ['label' => __('Akeneo Price Attribute Code (-EUR)')]);
        $this->addColumn('magento_type', ['label' => __('Magento Customer Group')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::_construct();
    }

    /**
     * renderCellTemplate function
     *
     * @param string $columnName
     * @return string
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName != 'magento_type' || !isset($this->_columns[$columnName])) {
            return parent::renderCellTemplate($columnName);
        }

        $options = $this->customerGroup->toOptionArray();
        $element = $this->elementFactory->create('select');
        $element->setForm(
            $this->getForm()
        )->setName(
            $this->_getCellInputElementName($columnName)
        )->setHtmlId(
            $this->_getCellInputElementId('<%- _id %>', $columnName)
        )->setValues(
            $options
        );

        return str_replace("\n", '', $element->getElementHtml());
    }
}

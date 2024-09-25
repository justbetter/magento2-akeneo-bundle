<?php

namespace JustBetter\AkeneoBundle\Block\Adminhtml\System\Config\Form\Field;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Tax\Model\TaxClass\Source\Product;

class TaxIdMapping extends AbstractFieldArray
{
    public function __construct(
        Context $context,
        protected Factory $elementFactory,
        protected Product $productTaxClassSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Initialise form fields
     */
    protected function _construct(): void
    {
        $this->addColumn('akeneo', ['label' => __('Akeneo Attribute Option Code')]);
        $this->addColumn('magento', ['label' => __('Magento')]);
        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add');

        parent::_construct();
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @throws Exception
     */
    public function renderCellTemplate($columnName): string
    {
        if ($columnName != 'magento' || !isset($this->_columns[$columnName])) {
            return parent::renderCellTemplate($columnName);
        }

        $options = $this->productTaxClassSource->getAllOptions();

        /** @var Select $element */
        $element = $this->elementFactory->create('select');

        $element->setForm($this->getForm())
            ->setName($this->_getCellInputElementName($columnName))
            ->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName))
            ->setValues($options);

        return str_replace("\n", '', $element->getElementHtml());
    }
}

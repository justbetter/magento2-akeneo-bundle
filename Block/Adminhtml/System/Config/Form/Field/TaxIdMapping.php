<?php

namespace JustBetter\AkeneoBundle\Block\Adminhtml\System\Config\Form\Field;

use Magento\Eav\Model\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Tax\Model\TaxClass\Source\Product;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Tax
 */
class TaxIdMapping extends AbstractFieldArray
{
    /**
     * This variable contains a Factory
     *
     * @var Factory $elementFactory
     */
    protected $elementFactory;

    /**
     * This variable contains a Product
     *
     * @var Product $productTaxClassSource
     */
    protected $productTaxClassSource;

    /**
     * Tax constructor.
     *
     * @param Context $context
     * @param Factory $elementFactory
     * @param Product $productTaxClassSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        Product $productTaxClassSource,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->elementFactory        = $elementFactory;
        $this->productTaxClassSource = $productTaxClassSource;
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _construct()
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
     *
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if (!in_array($columnName, ['magento']) || !isset($this->_columns[$columnName])) {
            return parent::renderCellTemplate($columnName);
        }

        /** @var array $options */
        $options = [];

        if (isset($this->_columns[$columnName])) {
            $options = $this->productTaxClassSource->getAllOptions();
        }

        /** @var Select $element */
        $element = $this->elementFactory->create('select');
        $element->setForm($this->getForm())
            ->setName($this->_getCellInputElementName($columnName))
            ->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName))
            ->setValues($options);

        return str_replace("\n", '', $element->getElementHtml());
    }
}

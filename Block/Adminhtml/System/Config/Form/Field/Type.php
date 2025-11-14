<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Block\Adminhtml\System\Config\Form\Field;

use Akeneo\Connector\Helper\Import\Attribute as AttributeHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form\Element\Select;

class Type extends AbstractFieldArray
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        protected ElementFactory $elementFactory,
        protected AttributeHelper $attributeHelper,
        protected Collection $customerGroup,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _construct(): void
    {
        $this->addColumn('pim_type', ['label' => __('Akeneo Price Attribute Code (-EUR)')]);
        $this->addColumn('magento_type', ['label' => __('Magento Customer Group')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = (string)__('Add');

        parent::_construct();
    }

    public function renderCellTemplate($columnName): string
    {
        if ($columnName !== 'magento_type' || !isset($this->_columns[$columnName])) {
            return parent::renderCellTemplate($columnName);
        }

        $options = $this->customerGroup->toOptionArray();

        /** @var Select $element */
        $element = $this->elementFactory->create('select');
        $element->setForm($this->getForm())
            ->setName($this->_getCellInputElementName($columnName))
            ->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName))
            ->setValues($options);

        return str_replace("\n", '', $element->getElementHtml());
    }
}

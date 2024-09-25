<?php

namespace JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Edit\Tab;

use IntlDateFormatter;
use JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Grid;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Block\Template\Context;
use JustBetter\AkeneoBundle\Model\Status;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * akeneo edit form main tab
 */
class Main extends Generic implements TabInterface
{
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        protected Store $systemStore,
        protected Status $status,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws LocalizedException
     */
    protected function _prepareForm(): static
    {
        $model = $this->_coreRegistry->registry('akeneo');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
                        
        $fieldset->addField(
            'import',
            'select',
            [
                'label' => __('Type'),
                'title' => __('Type'),
                'name' => 'import',
                'required' => true,
                'options' => Grid::getOptionArray0(),
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'code',
            'text',
            [
                'name' => 'code',
                'label' => __('Code'),
                'title' => __('Code'),
                'required' => true,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'entity_id',
            'text',
            [
                'name' => 'entity_id',
                'label' => __('Magento Entity ID'),
                'title' => __('Magento Entity ID'),
                'required' => true,
                'disabled' => false
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(
            IntlDateFormatter::MEDIUM
        );
        $timeFormat = $this->_localeDate->getTimeFormat(
            IntlDateFormatter::MEDIUM
        );

        $fieldset->addField(
            'created_at',
            'date',
            [
                'name'        => 'created_at',
                'label'       => __('Created'),
                'title'       => __('Created'),
                'date_format' => $dateFormat,
                'disabled' => false
            ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);
        
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     */
    public function getTabLabel(): Phrase|string
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     */
    public function getTabTitle(): Phrase|string
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden(): bool
    {
        return false;
    }

    /**
     * Check permission for passed action
     */
    protected function _isAllowedAction(string $resourceId): bool
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getTargetOptionArray(): array
    {
        return ['_self'  => 'Self', '_blank' => 'New Page'];
    }
}

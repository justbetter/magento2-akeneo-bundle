<?php

namespace JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Edit\Tab;

use IntlDateFormatter;
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
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var Status
     */
    protected $_status;

    /**
     * @param Context     $context
     * @param Registry    $registry
     * @param FormFactory $formFactory
     * @param Store       $systemStore
     * @param Status      $status
     * @param array       $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \JustBetter\AkeneoBundle\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('akeneo');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
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
                'options' => \JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Grid::getOptionArray0(),
                'disabled' => $isElementDisabled
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
                'disabled' => $isElementDisabled
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
                'disabled' => $isElementDisabled
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
                //'time_format' => $timeFormat,

                'disabled' => $isElementDisabled,
            ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);
        
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getTargetOptionArray()
    {
        return array(
            '_self'  => "Self",
            '_blank' => "New Page",
        );
    }
}

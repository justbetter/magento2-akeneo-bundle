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

namespace JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Edit\Tab;

use IntlDateFormatter;
use JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Grid;
use JustBetter\AkeneoBundle\Model\AkeneoFactory;
use JustBetter\AkeneoBundle\Model\Status;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Main extends Generic implements TabInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        protected AkeneoFactory $akeneoFactory,
        protected Store $systemStore,
        protected Status $status,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm(): Main
    {
        // Get model from registry (set by controller) or load from request
        $model = $this->_coreRegistry->registry('akeneo');
        
        if (!$model) {
            $model = $this->akeneoFactory->create();
            $id = (int)$this->getRequest()->getParam('id');
            
            if ($id) {
                $model->load($id); // @phpstan-ignore-line
            }
        }

        $isElementDisabled = false;

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

        $dateFormat = $this->_localeDate->getDateFormat(IntlDateFormatter::MEDIUM);

        $fieldset->addField(
            'created_at',
            'date',
            [
                'name' => 'created_at',
                'label' => __('Created'),
                'title' => __('Created'),
                'date_format' => $dateFormat,
                'disabled' => $isElementDisabled,
            ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel(): string
    {
        return (string)__('Item Information');
    }

    public function getTabTitle(): string
    {
        return (string)__('Item Information');
    }

    public function canShowTab(): bool
    {
        return true;
    }

    public function isHidden(): bool
    {
        return false;
    }

    protected function _isAllowedAction(string $resourceId): bool
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @return array<string, string>
     */
    public function getTargetOptionArray(): array
    {
        return [
            '_self' => 'Self',
            '_blank' => 'New Page',
        ];
    }
}

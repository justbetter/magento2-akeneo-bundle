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

namespace JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo;

use JustBetter\AkeneoBundle\Model\AkeneoFactory;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Escaper;
use Magento\Framework\Phrase;

class Edit extends Container
{
    protected Escaper $escaper;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        protected AkeneoFactory $akeneoFactory,
        array $data = []
    ) {
        $this->escaper = $context->getEscaper();
        parent::__construct($context, $data);
    }

    protected function _construct(): void
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'JustBetter_AkeneoBundle';
        $this->_controller = 'adminhtml_akeneo';

        parent::_construct();

        $this->buttonList->update('save', 'label', (string)__('Save Akeneo'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );

        $this->buttonList->update('delete', 'label', (string)__('Delete Akeneo'));
    }

    public function getHeaderText(): string
    {
        $id = (int)$this->getRequest()->getParam('id');
        
        if ($id) {
            $model = $this->akeneoFactory->create();
            $model->load($id); // @phpstan-ignore-line
            
            if ($model->getId()) {
                return (string)__("Edit Akeneo '%1'", $this->escaper->escapeHtml($model->getTitle()));
            }
        }

        return (string)__('New Akeneo');
    }

    protected function _getSaveAndContinueUrl(): string
    {
        return $this->getUrl('akeneomanager/*/save', [
            '_current' => true,
            'back' => 'edit',
            'active_tab' => '{{tab_id}}'
        ]);
    }

    protected function _prepareLayout(): Edit
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'content');
                }
            };
        ";

        return parent::_prepareLayout();
    }
}

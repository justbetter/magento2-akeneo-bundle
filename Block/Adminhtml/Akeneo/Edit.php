<?php

namespace JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;

class Edit extends Container
{
    public function __construct(
        Context $context,
        protected Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Initialize akeneo edit block
     */
    protected function _construct(): void
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'JustBetter_AkeneoBundle';
        $this->_controller = 'adminhtml_akeneo';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Akeneo'));
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

        $this->buttonList->update('delete', 'label', __('Delete Akeneo'));
    }

    /**
     * Retrieve text for header element depending on loaded post
     */
    public function getHeaderText(): Phrase
    {
        if ($this->coreRegistry->registry('akeneo')->getId()) {
            return __("Edit Akeneo '%1'", $this->escapeHtml($this->coreRegistry->registry('akeneo')->getTitle()));
        } else {
            return __('New Akeneo');
        }
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     */
    protected function _getSaveAndContinueUrl(): string
    {
        return $this->getUrl('akeneomanager/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }

    protected function _prepareLayout(): AbstractBlock
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

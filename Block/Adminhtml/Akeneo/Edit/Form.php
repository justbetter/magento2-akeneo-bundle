<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    protected function _prepareForm(): Form
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

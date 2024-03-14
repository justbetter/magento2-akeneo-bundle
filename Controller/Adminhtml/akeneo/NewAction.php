<?php

namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;

class NewAction extends Action
{
    public function __construct(
        Context $context,
        protected ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Forward to edit
     *
     * @return Forward
     */
    public function execute(): Forward
    {
        /** @var Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed(): bool
    {
        return true;
    }
}

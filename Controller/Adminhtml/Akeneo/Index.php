<?php

namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public function __construct(
        Context $context,
        private PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('JustBetter_AkeneoBundle::akeneo');
        $resultPage->addBreadcrumb(__('JustBetter'), __('JustBetter'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Manage Akeneo'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Akeneo'));

        return $resultPage;
    }
}
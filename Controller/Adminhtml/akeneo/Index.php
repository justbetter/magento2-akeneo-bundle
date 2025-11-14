<?php

namespace JustBetter\AkeneoBundle\Controller\Adminhtml\akeneo;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    public function __construct(
        Action\Context $context,
        protected PageFactory $resultPageFactory
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

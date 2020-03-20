<?php

namespace JustBetter\AkeneoBundle\Controller\Adminhtml\akeneo;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPagee;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('JustBetter_AkeneoBundle::akeneo');
        $resultPage->addBreadcrumb(__('JustBetter'), __('JustBetter'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Manage Akeneo'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Akeneo'));

        return $resultPage;
    }
}
?>
<?php
declare(strict_types=1);
namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

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
        $resultPage->addBreadcrumb((string)__('JustBetter'), (string)__('JustBetter'));
        $resultPage->addBreadcrumb((string)__('Manage item'), (string)__('Manage Akeneo'));
        $resultPage->getConfig()->getTitle()->prepend((string)__('Manage Akeneo'));

        return $resultPage;
    }
}

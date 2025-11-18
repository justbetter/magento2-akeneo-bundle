<?php
declare(strict_types=1);
namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use JustBetter\AkeneoBundle\Model\AkeneoFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action implements HttpGetActionInterface
{
    public function __construct(
        Action\Context $context,
        protected PageFactory $resultPageFactory,
        protected Registry $coreRegistry,
        protected AkeneoFactory $akeneoFactory,
        protected Session $session
    ) {
        parent::__construct($context);
    }

    protected function _isAllowed(): bool
    {
        return true;
    }

    protected function _initAction(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('JustBetter_AkeneoBundle::Akeneo')
            ->addBreadcrumb((string)__('JustBetter AkeneoBundle'), (string)__('JustBetter AkeneoBundle'))
            ->addBreadcrumb((string)__('Manage Item'), (string)__('Manage Item'));

        return $resultPage;
    }

    public function execute(): Page|Redirect
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->akeneoFactory->create();

        if ($id) {
            $model->load($id); // @phpstan-ignore-line
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage((string)__('This item no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->coreRegistry->register('akeneo', $model);

        /** @var Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->setActiveMenu('JustBetter_AkeneoBundle::Akeneo');
        $resultPage->addBreadcrumb((string)__('JustBetter'), (string)__('JustBetter'));
        $resultPage->addBreadcrumb(
            (string)($id ? __('Edit Item') : __('New Item')),
            (string)($id ? __('Edit Item') : __('New Item'))
        );
        $resultPage->getConfig()->getTitle()->prepend((string)($id ? __('Edit Item') : __('New Item')));

        return $resultPage;
    }
}

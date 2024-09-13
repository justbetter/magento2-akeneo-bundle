<?php

namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use JustBetter\AkeneoBundle\Model\Akeneo;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    public function __construct(
        Action\Context $context,
        protected PageFactory $resultPageFactory,
        protected Registry $coreRegistry
    ) {
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed(): bool
    {
        return true;
    }

    protected function _initAction(): Page
    {
        // load layout, set active menu and breadcrumbs
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('JustBetter_AkeneoBundle::Akeneo')
            ->addBreadcrumb(__('JustBetter AkeneoBundle'), __('JustBetter AkeneoBundle'))
            ->addBreadcrumb(__('Manage Item'), __('Manage Item'));

        return $resultPage;
    }

    /**
     * Edit Item
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(): Redirect|Page
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create(Akeneo::class);

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get(Session::class)->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->coreRegistry->register('akeneo', $model);

        // 5. Build edit form
        $resultPage = $this->_initAction();
        $resultPage->setActiveMenu('JustBetter_AkeneoBundle::Akeneo');
        $resultPage->addBreadcrumb(__('JustBetter'), __('JustBetter'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Item') : __('New Item'),
            $id ? __('Edit Item') : __('New Item')
        );
        $resultPage->getConfig()->getTitle()->prepend($id ? __('Edit Item') : __('New Item'));

        return $resultPage;
    }
}

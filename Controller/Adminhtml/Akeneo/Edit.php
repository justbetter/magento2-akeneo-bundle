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
            ->addBreadcrumb(__('JustBetter AkeneoBundle'), __('JustBetter AkeneoBundle'))
            ->addBreadcrumb(__('Manage Item'), __('Manage Item'));

        return $resultPage;
    }

    public function execute(): Page|Redirect
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->akeneoFactory->create();

        if ($id) {
            $model->load($id); // @phpstan-ignore-line
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
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
        $resultPage->addBreadcrumb(__('JustBetter'), __('JustBetter'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Item') : __('New Item'),
            $id ? __('Edit Item') : __('New Item')
        );
        $resultPage->getConfig()->getTitle()->prepend($id ? __('Edit Item') : __('New Item'));

        return $resultPage;
    }
}

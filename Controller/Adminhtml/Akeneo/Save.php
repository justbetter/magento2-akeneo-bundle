<?php

namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use JustBetter\AkeneoBundle\Model\Akeneo;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\Request;

class Save extends Action
{
    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create(Akeneo::class);

            $id = $request->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }
            
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Akeneo has been saved.'));
                $this->_objectManager->get(Session::class)->setFormData(false);
                if ($request->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException|\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Akeneo.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $request->getParam('id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}

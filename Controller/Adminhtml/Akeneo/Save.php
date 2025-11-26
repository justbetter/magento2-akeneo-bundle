<?php
declare(strict_types=1);
namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use JustBetter\AkeneoBundle\Model\AkeneoFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Save extends Action implements HttpPostActionInterface
{
    public function __construct(
        Action\Context $context,
        protected AkeneoFactory $akeneoFactory,
        protected Session $session
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $model = $this->akeneoFactory->create();

            $id = (int)$this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id); // @phpstan-ignore-line
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }

            $model->setData($data);

            try {
                $model->save(); // @phpstan-ignore-line
                $this->messageManager->addSuccessMessage((string)__('The Akeneo has been saved.'));
                $this->session->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, (string)__('Something went wrong while saving the Akeneo.'));
            }

            $this->session->setFormData($data);

            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}

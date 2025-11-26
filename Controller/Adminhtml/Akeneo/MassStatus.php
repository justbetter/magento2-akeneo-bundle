<?php
declare(strict_types=1);
namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use JustBetter\AkeneoBundle\Model\AkeneoFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;

class MassStatus extends Action implements HttpPostActionInterface
{
    public function __construct(
        Action\Context $context,
        protected AkeneoFactory $akeneoFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $itemIds = $this->getRequest()->getParam('akeneo');

        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addErrorMessage((string)__('Please select item(s).'));
        } else {
            try {
                $status = (int)$this->getRequest()->getParam('status');
                foreach ($itemIds as $itemId) {
                    $model = $this->akeneoFactory->create();
                    $model->load($itemId); // @phpstan-ignore-line
                    $model->setIsActive($status)->save(); // @phpstan-ignore-line
                }
                $this->messageManager->addSuccessMessage(
                    (string)__('A total of %1 record(s) have been updated.', count($itemIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('akeneomanager/*/index');
    }
}

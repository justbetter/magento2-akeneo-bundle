<?php

namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;

class MassStatus extends Action
{
    /**
     * @throws LocalizedException|Exception
     */
    public function execute(): Redirect
    {
        $itemIds = $this->getRequest()->getParam('akeneo');
        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                $status = (int) $this->getRequest()->getParam('status');
                foreach ($itemIds as $postId) {
                    $post = $this->_objectManager->get(\JustBetter\AkeneoBundle\Model\Akeneo::class)->load($postId);
                    $post->setIsActive($status)->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($itemIds))
                );
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        return $this->resultRedirectFactory->create()->setPath('akeneomanager/*/index');
    }

}

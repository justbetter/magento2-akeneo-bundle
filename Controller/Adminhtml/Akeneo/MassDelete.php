<?php

namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;

class MassDelete extends Action
{
    public function execute(): Redirect
    {
        $itemIds = $this->getRequest()->getParam('akeneo');
        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $post = $this->_objectManager->get(\JustBetter\AkeneoBundle\Model\Akeneo::class)->load($itemId);
                    $post->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($itemIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        return $this->resultRedirectFactory->create()->setPath('akeneomanager/*/index');
    }
}

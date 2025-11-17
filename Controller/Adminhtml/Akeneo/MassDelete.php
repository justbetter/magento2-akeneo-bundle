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
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;

class MassDelete extends Action implements HttpPostActionInterface
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
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $model = $this->akeneoFactory->create();
                    $model->load($itemId); // @phpstan-ignore-line
                    $model->delete(); // @phpstan-ignore-line
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($itemIds))
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

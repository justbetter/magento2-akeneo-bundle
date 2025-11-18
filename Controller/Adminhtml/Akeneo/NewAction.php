<?php
declare(strict_types=1);
namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;

class NewAction extends Action implements HttpGetActionInterface
{
    public function __construct(
        Action\Context $context,
        protected ForwardFactory $resultForwardFactory // @phpstan-ignore-line
    ) {
        parent::__construct($context);
    }

    public function execute(): Forward
    {
        /** @var Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create(); // @phpstan-ignore-line

        return $resultForward->forward('edit');
    }

    protected function _isAllowed(): bool
    {
        return true;
    }
}

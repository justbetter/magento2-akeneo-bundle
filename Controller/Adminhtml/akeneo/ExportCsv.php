<?php

namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use Exception;
use JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Grid;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class ExportCsv extends Action
{
    public function __construct(
        Context $context,
        protected FileFactory $fileFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @throws Exception
     */
    public function execute(): ResultInterface|ResponseInterface
    {
        $this->_view->loadLayout(false);

        $exportBlock = $this->_view->getLayout()->createBlock(Grid::class);

        return $this->fileFactory->create(
            'akeneo.csv',
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}

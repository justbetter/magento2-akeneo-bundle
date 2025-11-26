<?php
declare(strict_types=1);
namespace JustBetter\AkeneoBundle\Controller\Adminhtml\Akeneo;

use JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Grid;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\LayoutFactory;

class ExportCsv extends Action implements HttpGetActionInterface
{
    public function __construct(
        Action\Context $context,
        protected FileFactory $fileFactory,
        protected LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResponseInterface
    {
        $fileName = 'akeneo.csv';

        $layout = $this->layoutFactory->create();
        /** @var Grid $exportBlock */
        $exportBlock = $layout->createBlock(Grid::class);

        return $this->fileFactory->create(
            $fileName,
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}

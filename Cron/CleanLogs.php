<?php

namespace JustBetter\AkeneoBundle\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Akeneo\Connector\Model\ResourceModel\Log\CollectionFactory;
use Akeneo\Connector\Model\LogRepository;

class CleanLogs
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var LogRepository
     */
    protected $logRepository;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collectionFactory
     * @param LogRepository $logRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory,
        LogRepository $logRepository
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->logRepository = $logRepository;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if ($this->scopeConfig->isSetFlag('akeneo_connector/justbetter/log_cleaner/enable')) {
            $numberOfDays = (int) $this->scopeConfig->getValue('akeneo_connector/justbetter/log_cleaner/number_of_days');
            $date = date('Y-m-d', strtotime("-$numberOfDays days"));
            $logs = $this->collectionFactory->create()
                ->addFieldToFilter('created_at', ['lt' => $date])
                ->load();

            foreach ($logs->getItems() as $item) {
                $this->logRepository->delete($item);
            }
        }
    }
}

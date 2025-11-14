<?php

namespace JustBetter\AkeneoBundle\Job;

use Akeneo\Connector\Api\Data\ImportInterface;
use Akeneo\Connector\Model\ResourceModel\Log\Collection;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class SlackMessage
{
    protected StoreInterface $store;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->store = $storeManager->getStore();
    }

    public function success(): string
    {
        return ':white_check_mark: All of today\'s imports in *' . $this->store->getName()
            . '* have been successfully completed.';
    }

    public function warning(?Collection $errorLogs = null, ?Collection $processingLogs = null): string
    {
        $message = ':warning: *Warning!* There\'s a problem with today’s imports in *' . $this->store->getName()
            . "*.\n\n";
        $message .= (isset($errorLogs) && $errorLogs->getData())
            ? $this->logList($errorLogs, ImportInterface::IMPORT_ERROR)
            : '';
        $message .= (isset($processingLogs) && $processingLogs->getData())
            ? $this->logList($processingLogs, ImportInterface::IMPORT_PROCESSING)
            : '';

        return $message;
    }

    protected function logList(Collection $logs, int $status): string
    {
        $message = match ($status) {
            ImportInterface::IMPORT_ERROR => "The following imports have failed:\n\n",
            ImportInterface::IMPORT_PROCESSING => "The following imports are still in process:\n\n",
            default => '',
        };

        foreach ($logs->getData() as $log) {
            $message .= $this->formatList(date('H:i:s', strtotime($log['created_at'])), $log['name']);
        }

        return $message . "\n";
    }

    public function noImports(): string
    {
        return $this->warning() . 'No imports have been made today.';
    }

    protected function formatList(string $dateTime, string $name): string
    {
        return '> •  _' . $dateTime . '_ *' . $name . "*\n";
    }
}

<?php

namespace JustBetter\AkeneoBundle\Job;

use Akeneo\Connector\Api\Data\ImportInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Akeneo\Connector\Model\ResourceModel\Log\Collection;

class SlackMessage
{
    protected StoreInterface $store;

    /**
     * @throws NoSuchEntityException
     */
    public function __construct(
        StoreManagerInterface $store
    ) {
        $this->store = $store->getStore();
    }

    public function success(): string
    {
        return ':white_check_mark: All of today\'s imports in *' . $this->store->getName()
            . '* have been successfully completed.';
    }

    public function warning(Collection $errorLogs = null, Collection $processingLogs = null): string
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
        $message = '';
        switch ($status) {
            case $status == ImportInterface::IMPORT_ERROR:
                $message = "The following imports have failed:\n\n";
                break;
            case ImportInterface::IMPORT_PROCESSING:
                $message = "The following imports are still in process:\n\n";
                break;
        }
        foreach ($logs->getData() as $log) {
            $message .= $this->formatList(date('H:i:s', strtotime((string) $log['created_at'])), $log['name']);
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

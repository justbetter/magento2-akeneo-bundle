<?php

namespace JustBetter\AkeneoBundle\Job;

use Akeneo\Connector\Api\Data\ImportInterface;
use Magento\Store\Model\StoreManagerInterface;
use Akeneo\Connector\Model\ResourceModel\Log\Collection;

class MailMessage
{
    protected $store;

    public function __construct(StoreManagerInterface $store)
    {
        $this->store = $store->getStore();
    }

    /**
     * @return string
     */
    public function success()
    {
        return 'All of today\'s imports in <b>' . $this->store->getName()
            . '</b> have been successfully completed.';
    }

    /**
     * @param Collection $errorLogs
     * @param Collection $processingLogs
     * @return string
     */
    public function warning(Collection $errorLogs = null, Collection $processingLogs = null)
    {
        $message = '<b>Warning!</b> There\'s a problem with today’s imports in <b>' . $this->store->getName()
            . "</b>.\n\n";
        $message .= (isset($errorLogs) && $errorLogs->getData())
            ? $this->logList($errorLogs, ImportInterface::IMPORT_ERROR)
            : '';
        $message .= (isset($processingLogs) && $processingLogs->getData())
            ? $this->logList($processingLogs, ImportInterface::IMPORT_PROCESSING)
            : '';
        return $message;
    }

    /**
     * @param Collection $logs
     * @param int $status
     * @return string
     */
    protected function logList(Collection $logs, int $status)
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
            $message .= $this->formatList(date('H:i:s', strtotime($log['created_at'])), $log['name']);
        }
        return $message . "\n";
    }

    /**
     * @return string
     */
    public function noImports()
    {
        return $this->warning() . 'No imports have been made today.';
    }

    /**
     * @param string $dateTime
     * @param string $name
     * @return string
     */
    protected function formatList(string $dateTime, string $name)
    {
        return '> •  _' . $dateTime . '_ *' . $name . "*\n";
    }
}

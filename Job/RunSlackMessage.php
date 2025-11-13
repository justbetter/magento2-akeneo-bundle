<?php

namespace JustBetter\AkeneoBundle\Job;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Akeneo\Connector\Api\Data\ImportInterface;
use JustBetter\AkeneoBundle\Helper\SlackHelper;
use Akeneo\Connector\Model\ResourceModel\Log;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunSlackMessage
{
    protected $client;
    protected $helperData;
    protected $logCollection;
    protected $logs;
    protected $slackMessage;

    public function execute(?InputInterface $input = null, ?OutputInterface $output = null)
    {
        $message = $this->getMessage();
        if ($this->helperData->isEnable()) {
            $output ? $output->writeln($this->send($message)) : $this->send($message);
        } elseif ($output !== null) {
            $output->writeln(
                '<fg=red>This function has been disabled. ' . "\n"
                . 'To enable: Go to Stores/Configuration/Catalog/Akeneo Connector/JustBetter Akeneo/Slack and set Enabled to Yes</>'
            );
        }
    }

    public function __construct(Client $client, SlackHelper $helperData, Log\Collection $logCollection, SlackMessage $slackMessage)
    {
        $this->client = $client;
        $this->helperData = $helperData;
        $this->logCollection = $logCollection;
        $this->logs = $this->getLogs();
        $this->slackMessage = $slackMessage;
    }

    /**
     * Gets a collection of all Import logs of today
     * @return Log\Collection
     */
    protected function getLogs()
    {
        return $this->logCollection
            ->addFieldToFilter('created_at', ['gteq' => date('Y-m-d')])
            ->addFieldToFilter('created_at', ['lt' => date('Y-m-d', strtotime(date('Y-m-d') . ' +1 day'))]);
    }

    /**
     * Gets a collection of all Import logs of today with a specific status
     * @param int $status
     * @return Log\Collection
     */
    protected function getLogsByStatus(int $status)
    {
        $logs = clone $this->logs;
        return $logs->addFieldToFilter('status', $status);
    }

    /**
     * Checks if a log with a specific status exists in the collection
     * @param int $status
     * @return bool
     */
    protected function checkLogStatus(int $status)
    {
        foreach ($this->logs as $log) {
            if ($log->getStatus() == $status) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the message to be sent
     * @return string
     */
    protected function getMessage()
    {
        if (!$this->logs->getData()) {
            return $this->slackMessage->noImports();
        } elseif ($this->checkLogStatus(ImportInterface::IMPORT_ERROR) ||
            $this->checkLogStatus(ImportInterface::IMPORT_PROCESSING)) {
            return $this->slackMessage->warning(
                $this->getLogsByStatus(ImportInterface::IMPORT_ERROR),
                $this->getLogsByStatus(ImportInterface::IMPORT_PROCESSING)
            );
        }
        return $this->slackMessage->success();
    }

    /**
     * Sends the message to Slack
     * @param string $message
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function send(string $message)
    {
        try {
            $slackApi = $this->helperData->getGeneralConfig('api');
            $this->client->request('POST', $slackApi, [
                'form_params' => [
                    'token' => $this->helperData->getGeneralConfig('token'),
                    'channel' => $this->helperData->getGeneralConfig('channel'),
                    'text' => $message,
                    'username' => $this->helperData->getGeneralConfig('username')
                ]]);
            return '<info>✅ Message has been send to Slack channel: '
                . $this->helperData->getGeneralConfig('channel') . '</info>';
        } catch (RequestException $e) {
            $response =
                '<fg=red>⚠️  There\'s a problem with sending the message to Slack channel: '
                . $this->helperData->getGeneralConfig('channel') . " \n\n"
                . 'The following exception appeared:</>'
                . '<error>' . "\n\n" . $e->getResponse() . '</error>';
            return $response;
        }
    }
}

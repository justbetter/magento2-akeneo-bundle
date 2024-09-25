<?php

namespace JustBetter\AkeneoBundle\Job;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Akeneo\Connector\Api\Data\ImportInterface;
use JustBetter\AkeneoBundle\Helper\SlackHelper;
use Akeneo\Connector\Model\ResourceModel\Log;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunSlackMessage
{
    protected Log\Collection $logs;

    public function __construct(
        protected Client $client,
        protected SlackHelper $helperData,
        protected Log\Collection $logCollection,
        protected SlackMessage $slackMessage
    ) {
        $this->logs = $this->getLogs();
    }

    /**
     * @throws GuzzleException
     */
    public function execute(InputInterface $input = null, OutputInterface $output = null): void
    {
        $message = $this->getMessage();
        if ($this->helperData->isEnable()) {
            $output ? $output->writeln($this->send($message)) : $this->send($message);
        } else {
            $output->writeln(
                '<fg=red>This function has been disabled. ' . "\n"
                . 'To enable: Go to Stores/Configuration/Catalog/Akeneo Connector/JustBetter Akeneo/Slack and set Enabled to Yes</>'
            );
        }
    }

    /**
     * Gets a collection of all Import logs of today
     */
    protected function getLogs(): Log\Collection
    {
        return $this->logCollection
            ->addFieldToFilter('created_at', ['gteq' => date('Y-m-d')])
            ->addFieldToFilter('created_at', ['lt' => date('Y-m-d', strtotime(date('Y-m-d') . ' +1 day'))]);
    }

    /**
     * Gets a collection of all Import logs of today with a specific status
     */
    protected function getLogsByStatus(int $status): Log\Collection
    {
        $logs = clone $this->logs;
        return $logs->addFieldToFilter('status', $status);
    }

    /**
     * Checks if a log with a specific status exists in the collection
     */
    protected function checkLogStatus(int $status): bool
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
     */
    protected function getMessage(): string
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
     *
     * @throws GuzzleException
     */
    protected function send(string $message): string
    {
        $config = [
            'api' => $this->helperData->getGeneralConfig('api'),
            'token' => $this->helperData->getGeneralConfig('token'),
            'channel' => $this->helperData->getGeneralConfig('channel'),
            'username' => $this->helperData->getGeneralConfig('username')
        ];

        try {
            $this->client->request('POST', $config['api'], ['form_params' => [
                'token' => $config['token'],
                'channel' => $config['channel'],
                'text' => $message,
                'username' => $config['username']
            ]]);

            return "<info>✅ Message sent to Slack channel: {$config['channel']}</info>";
        } catch (RequestException $e) {
            $response = $e->getResponse() ? $e->getResponse()->getBody() : 'No response body';
            return "<fg=red>⚠️ Problem sending message to Slack channel: {$config['channel']}\n\n"
                . "Exception:\n<error>{$response}</error></>";
        }
    }
}

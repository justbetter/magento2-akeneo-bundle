<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Job;

use Akeneo\Connector\Api\Data\ImportInterface;
use Akeneo\Connector\Model\ResourceModel\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JustBetter\AkeneoBundle\Helper\SlackHelper;
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

    public function execute(?InputInterface $input = null, ?OutputInterface $output = null): void
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

    protected function getLogs(): Log\Collection
    {
        $tomorrow = strtotime(date('Y-m-d') . ' +1 day');
        
        return $this->logCollection
            ->addFieldToFilter('created_at', ['gteq' => date('Y-m-d')])
            ->addFieldToFilter('created_at', ['lt' => date('Y-m-d', $tomorrow !== false ? $tomorrow : time() + 86400)]);
    }

    protected function getLogsByStatus(int $status): Log\Collection
    {
        $logs = clone $this->logs;

        return $logs->addFieldToFilter('status', (string)$status);
    }

    protected function checkLogStatus(int $status): bool
    {
        foreach ($this->logs as $log) {
            if ($log->getStatus() === $status) {
                return true;
            }
        }

        return false;
    }

    protected function getMessage(): string
    {
        if (!$this->logs->getData()) {
            return $this->slackMessage->noImports();
        }

        if ($this->checkLogStatus(ImportInterface::IMPORT_ERROR) ||
            $this->checkLogStatus(ImportInterface::IMPORT_PROCESSING)) {
            return $this->slackMessage->warning(
                $this->getLogsByStatus(ImportInterface::IMPORT_ERROR),
                $this->getLogsByStatus(ImportInterface::IMPORT_PROCESSING)
            );
        }

        return $this->slackMessage->success();
    }

    protected function send(string $message): string
    {
        try {
            $slackApi = $this->helperData->getGeneralConfig('api');
            $this->client->request('POST', $slackApi, [
                'form_params' => [
                    'token' => $this->helperData->getGeneralConfig('token'),
                    'channel' => $this->helperData->getGeneralConfig('channel'),
                    'text' => $message,
                    'username' => $this->helperData->getGeneralConfig('username'),
                ]]);

            return '<info>✅ Message has been send to Slack channel: '
                . $this->helperData->getGeneralConfig('channel') . '</info>';
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $responseBody = $response ? (string)$response->getBody() : 'No response body';
            
            return '<fg=red>⚠️  There\'s a problem with sending the message to Slack channel: '
                . $this->helperData->getGeneralConfig('channel') . " \n\n"
                . 'The following exception appeared:</>'
                . '<error>' . "\n\n" . $responseBody . '</error>';
        }
    }
}

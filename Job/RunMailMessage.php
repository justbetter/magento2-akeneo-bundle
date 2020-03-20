<?php
namespace JustBetter\AkeneoBundle\Job;

use GuzzleHttp\Client;
use Zend_Mail as mail;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use GuzzleHttp\Exception\RequestException;
use JustBetter\AkeneoBundle\Job\MailMessage;
use Akeneo\Connector\Model\ResourceModel\Log;
use Akeneo\Connector\Api\Data\ImportInterface;
use JustBetter\AkeneoBundle\Helper\MailHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunMailMessage
{
    protected $client;
    protected $helperData;
    protected $logCollection;
    protected $logs;
    protected $mailMessage;
    protected $mailInterface;

    public function execute(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->state->setAreaCode(Area::AREA_FRONTEND);
        $message = $this->getMessage();
        if ($this->helperData->isEnable()) {
            $output ? $output->writeln($this->send($message)) : $this->send($message);
        } else {
            $output->writeln(
                '<fg=red>This function has been disabled. ' . "\n"
                . 'To enable: Go to Stores/Configuration/Catalog/Akeneo Connector/JustBetter Akeneo/mail and set Enabled to Yes</>'
            );
        }
    }

    public function __construct(
        Client $client,
        MailHelper $helperData,
        Log\Collection $logCollection,
        MailMessage $mailMessage,
        State $state
    ) {
        $this->client = $client;
        $this->helperData = $helperData;
        $this->logCollection = $logCollection;
        $this->logs = $this->getLogs();
        $this->state = $state;
        $this->mailMessage = $mailMessage;
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
            return $this->mailMessage->noImports();
        } elseif ($this->checkLogStatus(ImportInterface::IMPORT_ERROR) ||
            $this->checkLogStatus(ImportInterface::IMPORT_PROCESSING)) {
            return $this->mailMessage->warning(
                $this->getLogsByStatus(ImportInterface::IMPORT_ERROR),
                $this->getLogsByStatus(ImportInterface::IMPORT_PROCESSING)
            );
        }
        return $this->mailMessage->success();
    }

    /**
     * Sends the message to Mail
     * @param string $message
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function send(string $message)
    {
        try {
            $from = $this->helperData->getGeneralConfig('frommail');
            $nameFrom = $this->helperData->getGeneralConfig('fromname');
            $to = $this->helperData->getGeneralConfig('mail');
            $nameTo = $this->helperData->getGeneralConfig('mail');

            $email = new mail();
            $email->setSubject("Akeneo import notification");
            $email->setBodyHtml($message);
            $email->setFrom($from, $nameFrom);
            $email->addTo($to, $nameTo);
            $email->send();

            return '<info>✅ Message has been send to your mail: '
                . $this->helperData->getGeneralConfig('mail') . '</info>';
        } catch (RequestException $e) {
            $response =
                '<fg=red>⚠️  There\'s a problem with sending the message to your mail: '
                . $this->helperData->getGeneralConfig('mail') . " \n\n"
                . 'The following exception appeared:</>'
                . '<error>' . "\n\n" . $e->getResponse() . '</error>';
            return $response;
        }
    }
}

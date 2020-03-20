<?php

namespace JustBetter\AkeneoBundle\Console\Command;

use Symfony\Component\Console\Command\Command;
use JustBetter\AkeneoBundle\Job\RunMailMessage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MailNotificationCommand
 * @package JustBetter\AkeneoBundle\Console\Command
 */
class MailNotificationCommand extends Command
{
    protected $runMailMessage;

    public function __construct(RunMailMessage $runMailMessage, $name = null)
    {
        $this->runMailMessage = $runMailMessage;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('mail:imports');
        $this->setDescription(
            'This will check the status of today\'s Akeneo imports and send the results to your mail'
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runMailMessage->execute($input, $output);
    }
}

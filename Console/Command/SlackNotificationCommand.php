<?php

namespace JustBetter\AkeneoBundle\Console\Command;

use Symfony\Component\Console\Command\Command;
use JustBetter\AkeneoBundle\Job\RunSlackMessage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SlackNotificationCommand
 * @package JustBetter\AkeneoBundle\Console\Command
 */
class SlackNotificationCommand extends Command
{
    protected $runSlackMessage;

    public function __construct(RunSlackMessage $runSlackMessage, $name = null)
    {
        $this->runSlackMessage = $runSlackMessage;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('slack:imports');
        $this->setDescription(
            'This will check the status of today\'s Akeneo imports and send the results as a notification message to Slack'
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runSlackMessage->execute($input, $output);
    }
}

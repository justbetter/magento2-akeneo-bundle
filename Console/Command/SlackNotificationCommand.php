<?php

namespace JustBetter\AkeneoBundle\Console\Command;

use JustBetter\AkeneoBundle\Job\RunSlackMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SlackNotificationCommand extends Command
{
    public function __construct(
        protected RunSlackMessage $runSlackMessage,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('slack:imports');
        $this->setDescription(
            'This will check the status of today\'s Akeneo imports and send the results as a notification message to Slack'
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->runSlackMessage->execute($input, $output);

        return self::SUCCESS;
    }
}

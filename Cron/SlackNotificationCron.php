<?php

namespace JustBetter\AkeneoBundle\Api\Cron;

use Psr\Log\LoggerInterface;
use JustBetter\AkeneoBundle\Job\RunSlackMessage;

class SlackNotificationCron
{
    protected $runSlackMessage;

    public function __construct(RunSlackMessage $runSlackMessage)
    {
        $this->runSlackMessage = $runSlackMessage;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute()
    {
        $this->runSlackMessage->execute();
    }
}

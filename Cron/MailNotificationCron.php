<?php

namespace JustBetter\AkeneoBundle\Cron;

use JustBetter\AkeneoBundle\Job\RunMailMessage;

class MailNotificationCron
{
    protected $runMailMessage;

    public function __construct(RunMailMessage $runMailMessage)
    {
        $this->runMailMessage = $runMailMessage;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute()
    {
        $this->runMailMessage->execute();
    }
}

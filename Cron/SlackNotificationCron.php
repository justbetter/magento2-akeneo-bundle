<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Cron;

use JustBetter\AkeneoBundle\Job\RunSlackMessage;

class SlackNotificationCron
{
    public function __construct(
        protected RunSlackMessage $runSlackMessage
    ) {
    }

    public function execute(): void
    {
        $this->runSlackMessage->execute();
    }
}

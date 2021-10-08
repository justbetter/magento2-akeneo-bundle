<?php

namespace JustBetter\AkeneoBundle\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use JustBetter\AkeneoBundle\Job\SetNotVisible as SetNotVisibleJob;

class SetNotVisible implements ObserverInterface
{
    protected SetNotVisibleJob $job;

    public function __construct(SetNotVisibleJob $job)
    {
        $this->job = $job;
    }

    public function execute(Observer $observer): void
    {
        $this->job->execute();
    }
}

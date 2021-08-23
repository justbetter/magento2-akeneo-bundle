<?php

namespace JustBetter\AkeneoBundle\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use JustBetter\AkeneoBundle\Job\ImportMetricUnits as ImportMetricUnitsJob;

class ImportMetricUnits implements ObserverInterface
{
    protected ImportMetricUnitsJob $job;

    public function __construct(ImportMetricUnitsJob $job)
    {
        $this->job = $job;
    }

    public function execute(Observer $observer): void
    {
        $this->job->execute();
    }
}

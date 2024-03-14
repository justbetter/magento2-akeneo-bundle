<?php

namespace JustBetter\AkeneoBundle\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use JustBetter\AkeneoBundle\Job\ImportMetricUnits as ImportMetricUnitsJob;

class ImportMetricUnits implements ObserverInterface
{
    public function __construct(
        protected ImportMetricUnitsJob $job
    ) {
    }

    public function execute(Observer $observer): void
    {
        $this->job->execute();
    }
}

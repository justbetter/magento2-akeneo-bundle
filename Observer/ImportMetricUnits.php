<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Observer;

use JustBetter\AkeneoBundle\Job\ImportMetricUnits as ImportMetricUnitsJob;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

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

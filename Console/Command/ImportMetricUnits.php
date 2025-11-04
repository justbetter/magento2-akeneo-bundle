<?php

namespace JustBetter\AkeneoBundle\Console\Command;

use JustBetter\AkeneoBundle\Job\ImportMetricUnits as ImportMetricUnitsJob;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMetricUnits extends Command
{
    protected ImportMetricUnitsJob $job;

    public function __construct(ImportMetricUnitsJob $job, ?string $name = null)
    {
        $this->job = $job;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('metric:import');
        $this->setDescription('Add metric units from Akeneo to Magento attributes');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->job->execute($output);
    }
}

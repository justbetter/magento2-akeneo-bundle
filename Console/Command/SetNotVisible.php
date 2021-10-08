<?php

namespace JustBetter\AkeneoBundle\Console\Command;

use JustBetter\AkeneoBundle\Job\SetNotVisible as SetNotVisibleJob;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetNotVisible extends Command
{
    protected SetNotVisibleJob $job;

    public function __construct(SetNotVisibleJob $job, string $name = null)
    {
        $this->job = $job;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('set:notvisible');
        $this->setDescription('Set recently updated products to not visible');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Starting');

        $this->job->execute($output);

        $output->writeln('Finished!');
    }
}

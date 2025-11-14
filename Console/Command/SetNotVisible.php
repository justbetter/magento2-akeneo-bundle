<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Console\Command;

use JustBetter\AkeneoBundle\Job\SetNotVisible as SetNotVisibleJob;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetNotVisible extends Command
{
    public function __construct(
        protected SetNotVisibleJob $job,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('akeneo:setfamilynotvisible');
        $this->setDescription('Set family products to not visible');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting');

        $this->job->execute($output);

        $output->writeln('Finished!');

        return self::SUCCESS;
    }
}

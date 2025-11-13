<?php

namespace JustBetter\AkeneoBundle\Observer;

use Akeneo\Connector\Executor\JobExecutor;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ImportFinished implements ObserverInterface
{
    public function __construct(
        protected EventManager $eventManager
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var JobExecutor $executor */
        $executor = $observer->getData('import');

        $method = $executor->getMethod();

        // Dispatch custom event before cleanCache step (last step before completion)
        if ($method === 'cleanCache') {
            $code = $executor->getCurrentJob()->getCode();
            $this->eventManager->dispatch('akeneo_connector_import_finish_' . $code);
        }
    }
}

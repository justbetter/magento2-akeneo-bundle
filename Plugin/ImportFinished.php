<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Magento\Framework\Event\ManagerInterface as EventManager;

class ImportFinished
{
    public function __construct(
        protected EventManager $eventManager
    ) {
    }

    public function beforeCleanCache($subject): null
    {
        $this->eventManager->dispatch('akeneo_connector_import_finish_' . $subject->getCode());

        return null;
    }
}

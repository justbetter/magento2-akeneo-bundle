<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Magento\Framework\Event\ManagerInterface as EventManager;

class ImportFinished
{
    protected $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function beforeCleanCache($subject)
    {
        $this->eventManager->dispatch('akeneo_connector_import_finish_' . $subject->getCode());

        return null;
    }
}

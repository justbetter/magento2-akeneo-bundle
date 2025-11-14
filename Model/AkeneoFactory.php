<?php

namespace JustBetter\AkeneoBundle\Model;

use Magento\Framework\ObjectManagerInterface;

class AkeneoFactory
{
    public function __construct(
        protected ObjectManagerInterface $objectManager
    ) {
    }

    public function create(array $arguments = []): Akeneo
    {
        return $this->objectManager->create(Akeneo::class, $arguments);
    }
}

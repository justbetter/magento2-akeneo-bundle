<?php

namespace JustBetter\AkeneoBundle\Model;

class AkeneoFactory
{
    public function __construct(
        protected Akeneo $akeneo
    ) {
    }

    public function create(): Akeneo
    {
        return $this->akeneo;
    }
}
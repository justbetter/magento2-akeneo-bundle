<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Data;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class FamilyOptions implements OptionSourceInterface
{
    public function __construct(
        protected CollectionFactory $collectionFactory
    ) {
    }

    public function toOptionArray(): array
    {
        return array_map(fn($set) => [
            'value' => $set->getData('attribute_set_id'),
            'label' => $set->getData('attribute_set_name'),
        ], $this->collectionFactory->create()->getItems());
    }
}

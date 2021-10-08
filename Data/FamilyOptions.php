<?php

namespace JustBetter\AkeneoBundle\Data;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class FamilyOptions implements OptionSourceInterface
{
    protected CollectionFactory $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray(): array
    {
        return array_map(function ($set) {
            return [
                'value' => $set->getData('attribute_set_id'),
                'label' => $set->getData('attribute_set_name')
            ];
        }, $this->collectionFactory->create()->getItems());
    }
}
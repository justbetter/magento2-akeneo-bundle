<?php
/**
 * JustBetter Magento2 Akeneo Bundle
 *
 * @author JustBetter B.V.
 * @copyright Copyright (c) JustBetter B.V. (https://justbetter.nl)
 * @package Magento2 Akeneo Bundle
 *
 * Licensed under the GNU General Public License v3.0 or later.
 * For full license information, see the LICENSE file
 * or visit <https://github.com/justbetter/magento2-akeneo-bundle/blob/master/LICENSE>.
 */

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Data;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class FamilyOptions implements OptionSourceInterface
{
    public function __construct(
        protected CollectionFactory $collectionFactory // @phpstan-ignore-line
    ) {
    }

    /**
     * @return array<int, array{value: int|string|null, label: string|null}>
     */
    public function toOptionArray(): array
    {
        return array_map(fn ($set) => [
            'value' => $set->getData('attribute_set_id'),
            'label' => $set->getData('attribute_set_name'),
        ], $this->collectionFactory->create()->getItems()); // @phpstan-ignore-line
    }
}

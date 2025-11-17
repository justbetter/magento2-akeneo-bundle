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

namespace JustBetter\AkeneoBundle\Job;

use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetNotVisible
{
    protected const CONFIG_PREFIX = 'akeneo_connector/justbetter/';
    protected const NOT_VISIBLE_CONFIG_KEY = 'notvisiblefamilies';

    public function __construct(
        protected CollectionFactory $collectionFactory, // @phpstan-ignore-line
        protected ScopeConfigInterface $config,
        protected Action $action
    ) {
    }

    public function execute(?OutputInterface $output = null): void
    {
        $products = $this->collectionFactory->create() // @phpstan-ignore-line
            ->addFieldToFilter('attribute_set_id', ['in' => $this->getNotVisibleFamilies()])
            ->addFieldToFilter('visibility', ['neq' => '1'])
            ->getItems();

        if (count($products) === 0) {
            if ($output) {
                $output->writeln('No updates necessary');
            }

            return;
        }

        if ($output) {
            $output->writeln('Found ' . count($products) . ' products that should have visibility set to not visible individually');
        }

        $entityIds = array_map(fn ($p) => $p->getEntityId(), $products);

        $this->action->updateAttributes($entityIds, ['visibility' => '1'], 0);
    }

    /**
     * @return array<int, string>
     */
    protected function getNotVisibleFamilies(): array
    {
        return explode(
            ',',
            $this->config->getValue(static::CONFIG_PREFIX . static::NOT_VISIBLE_CONFIG_KEY) ?? ''
        );
    }
}

<?php

namespace JustBetter\AkeneoBundle\Job;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;;
use Symfony\Component\Console\Output\OutputInterface;

class SetNotVisible
{
    protected const CONFIG_PREFIX = 'akeneo_connector/justbetter/';
    protected const NOT_VISIBLE_CONFIG_KEY = 'notvisiblefamilies';

    public function __construct(
        protected CollectionFactory $collectionFactory,
        protected ScopeConfigInterface $config,
        protected Action $action
    ) {
    }

    /**
     * @throws Exception
     */
    public function execute(OutputInterface $output = null): void
    {
        $products = $this->collectionFactory->create()
            ->addFieldToFilter('attribute_set_id', ['in' => $this->getNotVisibleFamilies()])
            ->addFieldToFilter('visibility', ['neq' => '1'])
            ->getItems();

        if ((is_countable($products) ? count($products) : 0) == 0) {
            $output?->writeln('No updates necessary');
            return;
        }

        $output?->writeln('Found ' . (is_countable($products) ? count($products) : 0) . ' products that should have visibility set to not visible individually');

        $entityIds = array_map(fn($p) => $p->getEntityId(), $products);

        $this->action->updateAttributes($entityIds, ['visibility' => '1'], 0);
    }

    protected function getNotVisibleFamilies(): array
    {
        return explode(
            ',',
            $this->config->getValue(static::CONFIG_PREFIX . static::NOT_VISIBLE_CONFIG_KEY) ?? ''
        );
    }
}

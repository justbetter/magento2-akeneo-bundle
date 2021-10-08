<?php

namespace JustBetter\AkeneoBundle\Job;

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Akeneo\Pim\ApiClient\Pagination\ResourceCursor;
use Akeneo\Pim\ApiClient\Search\SearchBuilder;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Output\OutputInterface;

class SetNotVisible
{
    protected const CONFIG_PREFIX = 'akeneo_connector/justbetter/';
    protected const NOT_VISIBLE_CONFIG_KEY = 'notvisiblefamilies';

    protected CollectionFactory $collectionFactory;
    protected ScopeConfigInterface $config;
    protected Action $action;

    public function __construct(CollectionFactory $collectionFactory, ScopeConfigInterface $config, Action $action)
    {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->action = $action;
    }

    public function execute(OutputInterface $output = null): void
    {
        $products = $this->collectionFactory->create()
            ->addFieldToFilter('attribute_set_id', ['in' => $this->getNotVisibleFamilies()])
            ->addFieldToFilter('visibility', ['neq' => '1'])
            ->getItems();

        if (count($products) == 0) {
            if ($output) {
                $output->writeln('No updates necessary');
            }
            return;
        }

        if ($output) {
            $output->writeln('Found ' . count($products) . ' products that should have visibility set to not visible individually');
        }

        $entityIds = array_map(function ($p) {
            return $p->getEntityId();
        }, $products);

        $this->action->updateAttributes($entityIds, ['visibility' => '1'], 0);
    }

    protected function getNotVisibleFamilies(): array
    {
        return explode(
            ',',
            $this->config->getValue(static::CONFIG_PREFIX . static::NOT_VISIBLE_CONFIG_KEY)
        );
    }
}

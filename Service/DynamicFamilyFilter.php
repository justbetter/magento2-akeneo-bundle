<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Service;

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Model\Source\Filters\Update;
use Akeneo\Pim\ApiClient\Search\SearchBuilder;
use Akeneo\Pim\ApiClient\Search\SearchBuilderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DynamicFamilyFilter
{
    public const CONFIG_PATH_ENABLED = 'akeneo_connector/products_filters/dynamic_families_enabled';

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected Authenticator $authenticator,
        protected ConfigHelper $configHelper,
        protected ResourceConnection $resourceConnection,
        protected TimezoneInterface $timezone,
        protected SearchBuilderFactory $searchBuilderFactory
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_ENABLED);
    }

    /**
     * @return array<string>|null
     */
    public function getFamiliesWithUpdatedProducts(): ?array
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $client = $this->authenticator->getAkeneoApiClient();
        if ($client === null) {
            return null;
        }

        $searchBuilder = $this->buildUpdateFilter();
        if ($searchBuilder === null) {
            return null;
        }

        $families = [];
        $filters = ['search' => $searchBuilder->getFilters()];
        $pageSize = $this->configHelper->getPaginationSize();

        foreach ($client->getProductApi()->all($pageSize, $filters) as $product) {
            if (!empty($product['family'])) {
                $families[] = $product['family'];
            }
        }

        foreach ($client->getProductModelApi()->all($pageSize, $filters) as $model) {
            if (!empty($model['family'])) {
                $families[] = $model['family'];
            }
        }

        return array_values(array_unique($families));
    }

    protected function buildUpdateFilter(): ?SearchBuilder
    {
        $searchBuilder = $this->searchBuilderFactory->create();

        return match ($this->configHelper->getUpdatedMode()) {
            Update::GREATER_THAN => $this->greaterThan($searchBuilder, $this->configHelper->getUpdatedGreaterFilter()),
            Update::LOWER_THAN => $this->lowerThan($searchBuilder, $this->configHelper->getUpdatedLowerFilter()),
            Update::BETWEEN => $this->between(
                $searchBuilder,
                $this->configHelper->getUpdatedBetweenAfterFilter(),
                $this->configHelper->getUpdatedBetweenBeforeFilter()
            ),
            Update::SINCE_LAST_N_DAYS => $this->sinceLastNDays($searchBuilder, $this->configHelper->getUpdatedSinceFilter()),
            Update::SINCE_LAST_N_HOURS => $this->sinceLastNHours($searchBuilder, $this->configHelper->getUpdatedSinceLastHoursFilter()),
            Update::SINCE_LAST_IMPORT => $this->sinceLastImport($searchBuilder),
            default => null,
        };
    }

    protected function greaterThan(SearchBuilder $searchBuilder, ?string $date): ?SearchBuilder
    {
        return $date ? $searchBuilder->addFilter('updated', '>', "$date 00:00:00") : null;
    }

    protected function lowerThan(SearchBuilder $searchBuilder, ?string $date): ?SearchBuilder
    {
        return $date ? $searchBuilder->addFilter('updated', '<', "$date 23:59:59") : null;
    }

    protected function between(SearchBuilder $searchBuilder, ?string $after, ?string $before): ?SearchBuilder
    {
        return ($after && $before)
            ? $searchBuilder->addFilter('updated', 'BETWEEN', ["$after 00:00:00", "$before 23:59:59"])
            : null;
    }

    protected function sinceLastNDays(SearchBuilder $searchBuilder, ?string $days): ?SearchBuilder
    {
        if (!$days || !is_numeric($days)) {
            return null;
        }
        $date = $this->timezone->date()->modify("-$days days");

        return $searchBuilder->addFilter('updated', '>', $date->format('Y-m-d H:i:s'));
    }

    protected function sinceLastNHours(SearchBuilder $searchBuilder, ?string $hours): ?SearchBuilder
    {
        if (!$hours || !is_numeric($hours)) {
            return null;
        }
        $date = $this->timezone->date()->modify("-$hours hours");

        return $searchBuilder->addFilter('updated', '>', $date->format('Y-m-d H:i:s'));
    }

    protected function sinceLastImport(SearchBuilder $searchBuilder): ?SearchBuilder
    {
        $connection = $this->resourceConnection->getConnection();
        $date = $connection->fetchOne(
            $connection->select()
                ->from($this->resourceConnection->getTableName('akeneo_connector_job'), ['last_success_date'])
                ->where('code = ?', 'product')
        );

        return $date ? $searchBuilder->addFilter('updated', '>', $date) : null;
    }
}

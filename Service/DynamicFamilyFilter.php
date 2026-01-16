<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Service;

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Model\Source\Filters\Update;
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
        protected TimezoneInterface $timezone
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

        $filter = $this->buildUpdateFilter();
        if (empty($filter)) {
            return null;
        }

        $families = [];
        $searchFilters = ['updated' => [$filter]];
        $pageSize = $this->configHelper->getPaginationSize();

        foreach ($client->getProductApi()->all($pageSize, ['search' => $searchFilters]) as $product) {
            if (!empty($product['family'])) {
                $families[] = $product['family'];
            }
        }

        foreach ($client->getProductModelApi()->all($pageSize, ['search' => $searchFilters]) as $model) {
            if (!empty($model['family'])) {
                $families[] = $model['family'];
            }
        }

        return array_values(array_unique($families));
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildUpdateFilter(): array
    {
        return match ($this->configHelper->getUpdatedMode()) {
            Update::GREATER_THAN => $this->greaterThan($this->configHelper->getUpdatedGreaterFilter()),
            Update::LOWER_THAN => $this->lowerThan($this->configHelper->getUpdatedLowerFilter()),
            Update::BETWEEN => $this->between(
                $this->configHelper->getUpdatedBetweenAfterFilter(),
                $this->configHelper->getUpdatedBetweenBeforeFilter()
            ),
            Update::SINCE_LAST_N_DAYS => $this->sinceLastNDays($this->configHelper->getUpdatedSinceFilter()),
            Update::SINCE_LAST_N_HOURS => $this->sinceLastNHours($this->configHelper->getUpdatedSinceLastHoursFilter()),
            Update::SINCE_LAST_IMPORT => $this->sinceLastImport(),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function greaterThan(?string $date): array
    {
        return $date ? ['operator' => '>', 'value' => "$date 00:00:00"] : [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function lowerThan(?string $date): array
    {
        return $date ? ['operator' => '<', 'value' => "$date 23:59:59"] : [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function between(?string $after, ?string $before): array
    {
        return ($after && $before)
            ? ['operator' => 'BETWEEN', 'value' => ["$after 00:00:00", "$before 23:59:59"]]
            : [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sinceLastNDays(?string $days): array
    {
        if (!$days || !is_numeric($days)) {
            return [];
        }
        $date = $this->timezone->date()->modify("-$days days");

        return ['operator' => '>', 'value' => $date->format('Y-m-d H:i:s')];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sinceLastNHours(?string $hours): array
    {
        if (!$hours || !is_numeric($hours)) {
            return [];
        }
        $date = $this->timezone->date()->modify("-$hours hours");

        return ['operator' => '>', 'value' => $date->format('Y-m-d H:i:s')];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sinceLastImport(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $date = $connection->fetchOne(
            $connection->select()
                ->from($this->resourceConnection->getTableName('akeneo_connector_job'), ['last_success_date'])
                ->where('code = ?', 'product')
        );

        return $date ? ['operator' => '>', 'value' => $date] : [];
    }
}

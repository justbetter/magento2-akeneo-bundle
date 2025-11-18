<?php
declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Plugin\Helper\Import;

use Akeneo\Connector\Helper\Import\Product as ProductHelper;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Product
{
    /**
     * @var array<int, string>|null
     */
    protected ?array $codes = null;

    public function __construct(
        protected ScopeConfigInterface $config,
        protected StoreHelper $storeHelper
    ) {
    }

    /**
     * @param array<string, mixed> $result
     * @return array{0: array<string, mixed>, 1: string, 2: mixed}
     */
    public function beforeCreateTmpTableFromApi(ProductHelper $subject, array $result, string $tableSuffix, mixed $family = null): array
    {
        if (is_null($this->codes)) {
            $this->codes = explode(',', (string)$this->config->getValue('akeneo_connector/justbetter/important_attributes'));
        }

        if (!count($this->codes)) {
            return [$result, $tableSuffix, $family];
        }

        $stores = $this->storeHelper->getAllStores();
        $storeCodes = [];
        foreach ($stores as $local => $affectedStores) {
            foreach ($affectedStores as $affectedStore) {
                $storeCodes[$affectedStore['lang'] . '-' . $affectedStore['channel_code']] = [
                    $affectedStore['lang'],
                    $affectedStore['channel_code'],
                ];
            }
        }

        foreach ($this->codes as $code) {
            if (array_key_exists($code, $result['values'])) {
                continue;
            }
            $result['values'][$code] = [[
                'locale' => null,
                'scope' => null,
                'data' => null,
            ]];

            foreach ($storeCodes as $store) {
                $result['values'][$code][] = [
                    'locale' => $store[0],
                    'scope' => $store[1],
                    'data' => null,
                ];
            }
        }

        return [$result, $tableSuffix, $family];
    }
}

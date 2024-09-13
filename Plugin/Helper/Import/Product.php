<?php

namespace JustBetter\AkeneoBundle\Plugin\Helper\Import;

use Akeneo\Connector\Helper\Import\Product as AkeneoProduct;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

class Product
{
    protected ?array $codes = null;

    public function __construct(
        protected ScopeConfigInterface $config,
        protected StoreHelper $storeHelper,
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function beforeCreateTmpTableFromApi(
        AkeneoProduct $subject,
        array $result,
        string $tableSuffix,
        ?string $family = null
    ): array
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
                    $affectedStore['channel_code']
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

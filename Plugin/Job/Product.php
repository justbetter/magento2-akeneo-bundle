<?php

namespace JustBetter\AkeneoBundle\Plugin\Job;

use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Job\Product as AkeneoProduct;
use Akeneo\Connector\Model\Source\Filters\Family;
use Akeneo\Connector\Model\Source\Filters\Mode;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Product
{
    public const PRODUCTS_FILTERS_EXCLUDED_FAMILIES = 'akeneo_connector/products_filters/excluded_families';

    public function __construct(
        protected ConfigHelper $configHelper,
        protected ScopeConfigInterface $scopeConfig,
        protected Family $familyFilter
    ) {
    }

    public function getFamiliesToExport(): ?string
    {
        return $this->scopeConfig->getValue(self::PRODUCTS_FILTERS_EXCLUDED_FAMILIES);
    }

    public function aroundGetFamiliesToImport(
        AkeneoProduct $subject,
        callable $proceed
    ): array {
        $families = [];
        $familiesToExclude = explode(',', $this->getFamiliesToExport());

        $mode = $this->configHelper->getFilterMode();
        
        if ($mode == Mode::ADVANCED && empty($this->configHelper->getFamiliesFilter())) {
            $paginationSize = $this->configHelper->getPaginationSize();
            $apiFamilies = $subject->getAkeneoClient()->getFamilyApi()->all($paginationSize);

            foreach ($apiFamilies as $family) {
                if (isset($family['code'])) {
                    $families[] = $family['code'];
                }
            }
        } else {
            $families = $proceed();
        }
        
        if (!$families || $families[0] === '') {
            $families = array_values($this->familyFilter->getFamilies() ?? []);
        }

        return array_diff($families, $familiesToExclude);
    }
}
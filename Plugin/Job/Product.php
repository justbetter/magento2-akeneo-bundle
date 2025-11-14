<?php

namespace JustBetter\AkeneoBundle\Plugin\Job;

use Akeneo\Connector\Job\Product as AkeneoProduct;
use Akeneo\Connector\Model\Source\Filters\Family;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Product
{
    public const PRODUCTS_FILTERS_EXCLUDED_FAMILIES = 'akeneo_connector/products_filters/excluded_families';

    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected Family $familyFilter
    ) {
    }

    public function getFamiliesToExclude(): ?string
    {
        return $this->scopeConfig->getValue(self::PRODUCTS_FILTERS_EXCLUDED_FAMILIES);
    }

    public function afterGetFamiliesToImport(
        AkeneoProduct $subject,
        array $families
    ): array {
        $familiesToExclude = explode(',', $this->getFamiliesToExclude() ?? '');

        if (!$families || $families[0] === '') {
            $families = array_values($this->familyFilter->getFamilies() ?? []);
        }

        return array_diff($families, $familiesToExclude);
    }
}

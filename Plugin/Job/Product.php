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

    /**
     * @param array<int, string>|null $families
     * @return array<int, string>
     */
    public function afterGetFamilies(AkeneoProduct $subject, ?array $families = null): array
    {
        $familiesToExclude = explode(',', (string)$this->getFamiliesToExclude());

        if (!$families || $families[0] === '') {
            $allFamilies = $this->familyFilter->getFamilies();
            $families = is_array($allFamilies) ? array_values($allFamilies) : [];
        }

        return array_diff($families, $familiesToExclude);
    }
}

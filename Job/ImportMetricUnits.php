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

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface;
use Akeneo\Pim\ApiClient\Search\SearchBuilder;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMetricUnits
{
    protected const EAV_ATTRIBUTE_UNIT_FIELD = 'unit';
    protected const CONFIG_PREFIX = 'akeneo_connector/justbetter/';
    protected const ENABLED_CONFIG_KEY = 'enablemetricunits';
    protected const CHANNEL_CONFIG_KEY = 'metric_conversion_channel';

    public function __construct(
        protected Authenticator $authenticator,
        protected AttributeRepositoryInterface $attributeRepository,
        protected ScopeConfigInterface $config
    ) {
    }

    public function execute(?OutputInterface $output = null): void
    {
        if (!$this->authenticator->getAkeneoApiClient()) {
            if ($output) {
                $output->writeln('<error>Akeneo client not configured!</error>');
            }

            return;
        }

        if (!$this->config->getValue(static::CONFIG_PREFIX . static::ENABLED_CONFIG_KEY)) {
            if ($output) {
                $output->writeln('<error>Metrics not enabled!</error>');
            }

            return;
        }

        $conversions = $this->getChannelConversions();

        foreach ($this->getMetricAttributes() as $akeneoAttribute) {

            $code = $akeneoAttribute['code'];
            $unit = array_key_exists($code, $conversions)
                ? $conversions[$code]
                : $akeneoAttribute['default_metric_unit'];

            try {
                $magentoAttribute = $this->attributeRepository->get('catalog_product', $code);
            } catch (NoSuchEntityException $e) {
                if ($output) {
                    $output->writeln("<error>Skipping $code because it does not exist in Magento</error>");
                }

                continue;
            }

            if ($magentoAttribute->getData(self::EAV_ATTRIBUTE_UNIT_FIELD) == $unit) { // @phpstan-ignore-line
                continue;
            }

            $magentoAttribute->setData(self::EAV_ATTRIBUTE_UNIT_FIELD, $unit); // @phpstan-ignore-line
            $this->attributeRepository->save($magentoAttribute);

            if ($output) {
                $output->writeln("Set unit for <info>$code</info> to <info>$unit</info>");
            }
        }

        if ($output) {
            $output->writeln("<info>Done</info>");
        }
    }

    protected function getMetricAttributes(): ResourceCursorInterface
    {
        $search = (new SearchBuilder())->addFilter('type', 'IN', ['pim_catalog_metric']);

        return $this->authenticator->getAkeneoApiClient()->getAttributeApi()->all(100, ['search' => $search->getFilters()]);
    }

    /**
     * @return array<string, string>
     */
    protected function getChannelConversions(): array
    {
        $channel = $this->config->getValue(static::CONFIG_PREFIX . static::CHANNEL_CONFIG_KEY);

        return $this->authenticator->getAkeneoApiClient()->getChannelApi()->get($channel)['conversion_units'] ?? [];
    }
}

<?php

namespace JustBetter\AkeneoBundle\Job;

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface;
use Akeneo\Pim\ApiClient\Search\SearchBuilder;
use Exception;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMetricUnits
{
    protected const EAV_ATTRIBUTE_UNIT_FIELD = 'unit';
    protected const CONFIG_PREFIX = 'akeneo_connector/justbetter/';
    protected const ENABLED_CONFIG_KEY = 'enablemetricunits';
    protected const CHANNEL_CONFIG_KEY = 'metric_conversion_channel';
    protected ?AkeneoPimClientInterface $akeneoClient = null;

    /**
     * @throws Exception
     */
    public function __construct(
        Authenticator $authenticator,
        protected AttributeRepositoryInterface $attributeRepository,
        protected ScopeConfigInterface $config
    ) {
        if (($client = $authenticator->getAkeneoApiClient())) {
            $this->akeneoClient = $client;
        }
    }

    /**
     * @throws Exception
     */
    public function execute(OutputInterface $output = null): void
    {
        if (! $this->akeneoClient) {
            $output?->writeln('<error>Akeneo client not configured!</error>');
            return;
        }
        
        if (!$this->config->getValue(static::CONFIG_PREFIX . static::ENABLED_CONFIG_KEY)) {
            $output?->writeln('<error>Metrics not enabled!</error>');
            return;
        }

        $conversions = $this->getChannelConversions();

        foreach ($this->getMetricAttributes() as $akeneoAttribute) {

            $code = $akeneoAttribute['code'];
            $unit = array_key_exists($code, $conversions)
                ? $conversions[$code]
                : $akeneoAttribute['default_metric_unit'];

            try {
                /** @var Attribute $magentoAttribute */
                $magentoAttribute = $this->attributeRepository->get('catalog_product', $code);
            } catch (NoSuchEntityException) {
                $output?->writeln("<error>Skipping $code because it does not exist in Magento</error>");
                continue;
            }

            if ($magentoAttribute->getData(self::EAV_ATTRIBUTE_UNIT_FIELD) == $unit) {
                continue;
            }

            $magentoAttribute->setData(self::EAV_ATTRIBUTE_UNIT_FIELD, $unit);
            $magentoAttribute->save(); // @phpstan-ignore-line

            $output?->writeln("Set unit for <info>$code</info> to <info>$unit</info>");
        }

        $output?->writeln("<info>Done</info>");
    }

    protected function getMetricAttributes(): ResourceCursorInterface
    {
        $search = (new SearchBuilder())->addFilter('type', 'IN', ['pim_catalog_metric']);

        return $this->akeneoClient->getAttributeApi()->all(100, ['search' => $search->getFilters()]);
    }

    protected function getChannelConversions(): array
    {
        $channel = $this->config->getValue(static::CONFIG_PREFIX . static::CHANNEL_CONFIG_KEY);

        return $this->akeneoClient->getChannelApi()->get($channel)['conversion_units'] ?? [];
    }
}

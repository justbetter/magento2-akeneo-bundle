<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Job\Product;
use Akeneo\Connector\Helper\Config;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\ScopeInterface as scope;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;

class SetTaxClassId
{
    /**
     * This variable contains a ProductImportHelper
     *
     * @var ProductImportHelper $entitiesHelper
     */
    protected $entitiesHelper;

    /**
     * This variable contains a ConfigHelper
     *
     * @var ConfigHelper $configHelper
     */
    protected $configHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * column name of tax_id.
     * @var string
     */
    protected $tax_id_column;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @param ProductImportHelper $entitiesHelper
     * @param Json $serializer
     * @param ConfigHelper $configHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param EavConfig $eavConfig
     */
    public function __construct(
        ProductImportHelper $entitiesHelper,
        Json $serializer,
        ConfigHelper $configHelper,
        ScopeConfigInterface $scopeConfig,
        EavConfig $eavConfig
    ) {
        $this->entitiesHelper = $entitiesHelper;
        $this->configHelper = $configHelper;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Overwrite tax id with the one imported from Akeneo.
     *
     * @param Product $context
     */
    public function afterAddRequiredData(Product $context)
    {
        $extensionEnabled = $this->scopeConfig->getValue('akeneo_connector/justbetter/settaxclass', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return ;
        }

        $attributes = $this->serializer->unserialize(
            $this->scopeConfig->getValue(ConfigHelper::ATTRIBUTE_TYPES)
        );
        $mappings = $this->serializer->unserialize(
            $this->scopeConfig->getValue('akeneo_connector/product/tax_id_mapping')
        );

        foreach ($attributes as $attribute) {
            if ($attribute['magento_type'] === "tax") {
                $this->tax_id_column = $attribute['pim_type'];
                break;
            }
        }

        if (!$this->tax_id_column || !count($mappings)) {
            return;
        }

        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tmpTable */
        $tmpTable = $this->entitiesHelper->getTableName($context->getCode());
        $connection->query($this->createQuery($this->tax_id_column, $tmpTable));
    }

    /**
     * After tax_class_id has been set to normal option value
     * set it to correct value.
     *
     * @param Product $context
     * @return void
     */
    public function afterUpdateOption(Product $context)
    {
        if (!$this->tax_id_column) {
            return;
        }

        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tmpTable */
        $tmpTable = $this->entitiesHelper->getTableName($context->getCode());

        $connection->query('UPDATE ' . $tmpTable . ' SET `' . $this->tax_id_column . '` = `_tax_class_id`;');
    }

    /**
     * Create the query to update the rows.
     *
     * @param string $tax_id_column
     * @param string $tableName
     *
     * @return string
     */
    public function createQuery($tax_id_column, $tableName)
    {
        $query = "
            UPDATE `" . $tableName . "`
            SET `_tax_class_id` =
            ";

        $query = $this->addCase($query, $tax_id_column);

            return $query;
    }

    /**
     * Add the switch case to the query.
     *
     * @param string $query
     * @param string $tax_id_column
     *
     * @return string
     */
    public function addCase($query, $tax_id_column)
    {
        $query .= "CASE
        ";

        $mappings = $this->serializer->unserialize(
            $this->scopeConfig->getValue('akeneo_connector/product/tax_id_mapping')
        );
        foreach ($mappings as $mapping) {
            $query .= "WHEN `" . $tax_id_column . "` = '" . $mapping['akeneo'] . "' then '" . $mapping['magento'] . "'
            ";
        }

        $query .= 'ELSE `_tax_class_id`
        END';


        return $query;
    }
}

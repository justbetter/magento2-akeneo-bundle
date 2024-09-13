<?php

namespace JustBetter\AkeneoBundle\Helper\Import;

use Akeneo\Connector\Helper\Import\Product as BaseProduct;

class Product extends BaseProduct
{
    /**
     * Overwritten to set values for store view 0 when attributes are scopable/localizable and required
     */
    protected function getColumnsFromResult(array $result, array $keys = []): array
    {
        // This returns the result for the temp table DB columns. ex: 'name-nl_NL-ecommerce' => 'value'
        $mappedResult = parent::getColumnsFromResult($result, $keys);

        if (!$this->scopeConfig->getValue('akeneo_connector/justbetter/defaultstorevalues') || !array_key_exists('values', $result)) {
            return $mappedResult;
        }

        $requiredAttributes = $this->getRequiredAttributes();

        foreach ($requiredAttributes as $requiredAttribute) {

            if (
                !array_key_exists($requiredAttribute, $result['values']) ||
                (is_countable($result['values'][$requiredAttribute]) ? count($result['values'][$requiredAttribute]) : 0) == 0 ||
                !$this->isScopableOrLocalizable($requiredAttribute, $mappedResult)
            ) {
                continue;
            }

            $mappedResult[$requiredAttribute] = $this->getFirstValue($result['values'][$requiredAttribute]);
        }

        return $mappedResult;
    }

    protected function getFirstValue(array $values): mixed
    {
        $array = array_reverse($values);
        return array_pop($array)['data'] ?? '';
    }

    /**
     * Check if an attribute is scopeable or localizable based on the column result name, ex. name-nl_NL-ecommerce
     */
    protected function isScopableOrLocalizable(string $attributeCode, array $columnResult): bool
    {
        $columns = array_keys($columnResult);

        foreach ($columns as $column) {

            if ($column === $attributeCode) {
                return false;
            }

            if (str_starts_with($column, $attributeCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a list of Magento's required product attributes
     */
    protected function getRequiredAttributes(): array
    {
        $eavAttributeTable = $this->connection->getTableName('eav_attribute');
        $eavEntityTypeTable = $this->connection->getTableName('eav_entity_type');

        $select = $this->connection->select()
            ->from("$eavAttributeTable AS attr")
            ->join("$eavEntityTypeTable AS type",
                "attr.entity_type_id = type.entity_type_id AND type.entity_type_code = 'catalog_product'")
            ->where('is_required = 1');

        $requiredAttributes = $this->connection->fetchAll($select);

        return array_map(fn (array $attribute) => $attribute['attribute_code'], $requiredAttributes);
    }
}
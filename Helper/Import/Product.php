<?php

namespace JustBetter\AkeneoBundle\Helper\Import;

use Akeneo\Connector\Helper\Import\Product as BaseProduct;

class Product extends BaseProduct
{
    protected function getColumnsFromResult(array $result, array $keys = []): array
    {
        $mappedResult = parent::getColumnsFromResult($result, $keys);

        if (!$this->scopeConfig->getValue('akeneo_connector/justbetter/defaultstorevalues') || !array_key_exists('values', $result)) {
            return $mappedResult;
        }

        $adminChannel = $this->scopeConfig->getValue('akeneo_connector/akeneo_api/admin_channel');
        $defaultLanguage = $this->scopeConfig->getValue('akeneo_connector/justbetter/defaultlanguage');
        $requiredAttributes = $this->getRequiredAttributes();

        foreach ($requiredAttributes as $requiredAttribute) {
            if (!array_key_exists($requiredAttribute, $result['values']) ||
                count($result['values'][$requiredAttribute]) === 0 ||
                !$this->isScopableOrLocalizable($requiredAttribute, $mappedResult)
            ) {
                continue;
            }

            if (!array_key_exists($requiredAttribute . '-' . $defaultLanguage . '-' . $adminChannel, $mappedResult) && $defaultLanguage) {
                $mappedResult[$requiredAttribute . '-' . $defaultLanguage . '-' . $adminChannel] = $this->getFirstValue($result['values'][$requiredAttribute]);
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

    protected function getRequiredAttributes(): array
    {
        $eavAttributeTable = $this->connection->getTableName('eav_attribute');
        $eavEntityTypeTable = $this->connection->getTableName('eav_entity_type');

        $select = $this->connection->select()
            ->from("$eavAttributeTable AS attr")
            ->join(
                "$eavEntityTypeTable AS type",
                "attr.entity_type_id = type.entity_type_id AND type.entity_type_code = 'catalog_product'"
            )
            ->where('is_required = 1');

        $requiredAttributes = $this->connection->fetchAll($select);

        return array_map(fn (array $attribute) => $attribute['attribute_code'], $requiredAttributes);
    }
}

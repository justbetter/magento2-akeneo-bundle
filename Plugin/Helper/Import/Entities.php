<?php

namespace JustBetter\AkeneoBundle\Plugin\Helper\Import;

use Akeneo\Connector\Helper\Import\Attribute as AttributeHelper;
use Akeneo\Connector\Helper\Import\Entities as AkeneoEntities;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Store\Model\ScopeInterface as scope;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class Entities
{
    public function __construct(
        protected ScopeConfigInterface $config,
        protected StoreHelper $storeHelper,
        protected AttributeHelper $attributeHelper
    ) {
    }

    public function afterFormatMediaName(AkeneoEntities $subject, $result)
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/formatmedianame', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return $result;
        }

        return str_replace('_', '-', (string) $result);
    }

    /**
     * Before setting the values we use Tax Type value from Akeneo when available
     */
    public function beforeSetValues(
        AkeneoEntities $subject,
        string $jobCode,
        string $entityTable,
        array $data,
        int $entityTypeId,
        int $storeId,
        int $mode = AdapterInterface::INSERT_ON_DUPLICATE
    ): array
    {
        $additionalTypes = $this->attributeHelper->getAdditionalTypes();

        foreach ($additionalTypes as $key => $additionalType) {
            if ($additionalType ===  'tax' && isset($data[$key])) {
                if (isset($data['tax_class_id']) && $data['tax_class_id'] instanceof \Zend_Db_Expr) {
                    $defaultTaxClassId = $data['tax_class_id']->__toString();
                    $data['tax_class_id'] = new \Zend_Db_Expr(
                        "IF(`".$data[$key]."` IS NULL OR `".$data[$key]."` = '', '".$defaultTaxClassId."', `".$data[$key]."`)"
                    );
                } else {
                    $data['tax_class_id'] = new \Zend_Db_Expr(
                        "IF(`".$data[$key]."` IS NULL OR `".$data[$key]."` = '', `_tax_class_id`, `".$data[$key]."`)"
                    );
                }
            }
        }

        return [$jobCode, $entityTable, $data, $entityTypeId, $storeId, $mode];
    }
}

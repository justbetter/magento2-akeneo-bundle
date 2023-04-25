<?php

namespace JustBetter\AkeneoBundle\Plugin\Helper\Import;

use Magento\Store\Model\ScopeInterface as scope;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Akeneo\Connector\Helper\Import\Attribute as AttributeHelper;

class Entities
{

    public function __construct(
        protected ScopeConfigInterface $config,
        protected StoreHelper $storeHelper,
        protected AttributeHelper $attributeHelper
    ) {
    }

    public function afterFormatMediaName($subject, $result)
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/formatmedianame', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return $result;
        }

        return str_replace("_", "-", $result);
    }

    /**
     * Before setting the values we use Tax Type value from Akeneo when available
     *
     * @param $subject
     * @param $jobCode
     * @param $entityTable
     * @param $data
     * @param $entityTypeId
     * @param $storeId
     * @param $mode
     * @return array
     */
    public function beforesetValues($subject, $jobCode, $entityTable, $data, $entityTypeId, $storeId, $mode)
    {
        $additonalTypes = $this->attributeHelper->getAdditionalTypes();

        foreach ($additonalTypes as $key => $additonalType) {
            if ($additonalType ===  'tax' && isset($data[$key])) {
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

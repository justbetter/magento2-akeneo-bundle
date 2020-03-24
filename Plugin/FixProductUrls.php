<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Zend_Db_Expr as expression;
use Akeneo\Connector\Job\Product;
use Magento\Store\Model\ScopeInterface as scope;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;

/**
 * FixConfigurableProductUrls class
 */
class FixProductUrls
{
    protected $entitiesHelper;
    protected $storeHelper;
    protected $config;

    /**
     * construct function
     * @param ProductImportHelper $entitiesHelper
     */
    public function __construct(
        ProductImportHelper $entitiesHelper,
        StoreHelper $storeHelper,
        ScopeConfigInterface $config
    ) {
        $this->entitiesHelper = $entitiesHelper;
        $this->storeHelper = $storeHelper;
        $this->config = $config;
    }

    /**
     * afterCreateConfigurable function
     *
     * @param product $subject
     * @param bool $result
     * @return bool $result
     */
    public function afterCreateConfigurable(product $subject, $result)
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/fixconfigurableurls', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return $result;
        }
        $tmpTableName = $this->entitiesHelper->getTableName($subject->getCode());
        $this->fixDuplicateUrls($tmpTableName);
        return $result;
    }

    /**
     * fix Duplicate Urls function
     *
     * @param string $tmpTableName
     * @return void
     */
    public function fixDuplicateUrls($tmpTableName)
    {
        $stores = $this->storeHelper->getStores(['lang']);
        $connection = $this->entitiesHelper->getConnection();
        $identifier = " LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(`identifier`), ':', ''), ')', ''), '(', ''), ',', ''), '\/', ''), '\'', ''), '&', ''), '!', ''), '.', ''), ' ', '-'), '--', '-'), '--', '-')) ";

        foreach ($stores as $local) {
            $local = "url_key-" . $local[0]['lang'];
            $urlKey = $connection->tableColumnExists($tmpTableName, $local);

            if ($urlKey) {
                $data[$local] = new expression("CONCAT(`" . $local . "`,'-' ," . $identifier . ")");

                $where[] = "parent is not null";
                $where[] = "`$local` is not null";

                $connection->update(
                    $tmpTableName,
                    $data,
                    $where
                );
            }
        }
    }
}

<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\DB\Select;
use Akeneo\Connector\Helper\Authenticator;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\ScopeInterface as scope;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Output as OutputHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Akeneo\Connector\Job\Attribute as PimgentoAttribute;
use Akeneo\Connector\Helper\Import\Entities as EntitiesHelper;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Akeneo\Connector\Helper\Import\Attribute as AttributeHelper;

/**
 * Class Attribute
 *
 * @package JustBetter\AkeneoMetrics\Job
 * @SuppressWarnings(PHPMD)
 */
class Metrics extends PimgentoAttribute
{
    protected $code = 'attribute';
    protected $name = 'Attribute';
    protected $entitiesHelper;
    protected $configHelper;
    protected $eavConfig;
    protected $attributeHelper;
    protected $cacheTypeList;
    protected $storeHelper;
    protected $eavSetup;
    protected $config;

    public function __construct(
        OutputHelper $outputHelper,
        ManagerInterface $eventManager,
        Authenticator $authenticator,
        EntitiesHelper $entitiesHelper,
        ConfigHelper $configHelper,
        Config $eavConfig,
        AttributeHelper $attributeHelper,
        TypeListInterface $cacheTypeList,
        StoreHelper $storeHelper,
        EavSetup $eavSetup,
        ScopeConfigInterface $config,
        array $data = []
    ) {
        $this->entitiesHelper = $entitiesHelper;
        $this->configHelper = $configHelper;
        $this->eavConfig = $eavConfig;
        $this->attributeHelper = $attributeHelper;
        $this->cacheTypeList = $cacheTypeList;
        $this->storeHelper = $storeHelper;
        $this->eavSetup = $eavSetup;
        $this->config = $config;

        parent::__construct(
            $outputHelper,
            $eventManager,
            $authenticator,
            $entitiesHelper,
            $configHelper,
            $eavConfig,
            $attributeHelper,
            $cacheTypeList,
            $storeHelper,
            $eavSetup,
            $data
        );
    }

    /**
     * Add attributes if not exists
     *
     * @return void
     */
    public function addAttributes()
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/akeneometrics', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return parent::addAttributes();
        }
        
        /** @var array $columns */
        $columns = $this->attributeHelper->getSpecificColumns();
        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tmpTable */
        $tmpTable = $this->entitiesHelper->getTableName($this->getCode());

        /** @var string $adminLang */
        $adminLang = $this->storeHelper->getAdminLang();
        /** @var string $adminLabelColumn */
        $adminLabelColumn = sprintf('labels-%s', $adminLang);

        /** @var Select $import */
        $import = $connection->select()->from($tmpTable);
        /** @var \Zend_Db_Statement_Interface $query */
        $query = $connection->query($import);

        while (($row = $query->fetch())) {
            /* Insert base data (ignore if already exists) */
            /** @var string[] $values */
            $values = [
                'attribute_id'   => $row['_entity_id'],
                'entity_type_id' => $this->getEntityTypeId(),
                'attribute_code' => $row['code'],
            ];
            $connection->insertOnDuplicate(
                $this->entitiesHelper->getTable('eav_attribute'),
                $values,
                array_keys($values)
            );

            $values = [
                'attribute_id' => $row['_entity_id'],
            ];
            $connection->insertOnDuplicate(
                $this->entitiesHelper->getTable('catalog_eav_attribute'),
                $values,
                array_keys($values)
            );

            /* Retrieve default admin label */
            /** @var string $frontendLabel */
            $frontendLabel = __('Unknown');
            if (!empty($row[$adminLabelColumn])) {
                $frontendLabel = $row[$adminLabelColumn];
            }

            /* Retrieve attribute scope */
            /** @var int $global */
            $global = ScopedAttributeInterface::SCOPE_GLOBAL; // Global
            if ($row['scopable'] == 1) {
                $global = ScopedAttributeInterface::SCOPE_WEBSITE; // Website
            }
            if ($row['localizable'] == 1) {
                $global = ScopedAttributeInterface::SCOPE_STORE; // Store View
            }
            /** @var array $data */
            $data = [
                'entity_type_id' => $this->getEntityTypeId(),
                'attribute_code' => $row['code'],
                'frontend_label' => $frontendLabel,
                'is_global'      => $global,
            ];
            foreach ($columns as $column => $def) {
                if (!$def['only_init']) {
                    $data[$column] = $row[$column];
                }
            }

            // Add metric unit to eav attribute
            if (! empty($row['default_metric_unit'])) {
                $data['unit'] = $row['default_metric_unit'];
            }

             /** @var array $defaultValues */
            $defaultValues = [];
            if ($row['_is_new'] == 1) {
                $defaultValues = [
                    'backend_table'                 => null,
                    'frontend_class'                => null,
                    'is_required'                   => 0,
                    'is_user_defined'               => 1,
                    'default_value'                 => null,
                    'is_unique'                     => $row['unique'],
                    'note'                          => null,
                    'is_visible'                    => 1,
                    'is_system'                     => 1,
                    'input_filter'                  => null,
                    'multiline_count'               => 0,
                    'validate_rules'                => null,
                    'data_model'                    => null,
                    'sort_order'                    => 0,
                    'is_used_in_grid'               => 0,
                    'is_visible_in_grid'            => 0,
                    'is_filterable_in_grid'         => 0,
                    'is_searchable_in_grid'         => 0,
                    'frontend_input_renderer'       => null,
                    'is_searchable'                 => 0,
                    'is_filterable'                 => 0,
                    'is_comparable'                 => 0,
                    'is_visible_on_front'           => 0,
                    'is_wysiwyg_enabled'            => 0,
                    'is_html_allowed_on_front'      => 0,
                    'is_visible_in_advanced_search' => 0,
                    'is_filterable_in_search'       => 0,
                    'used_in_product_listing'       => 0,
                    'used_for_sort_by'              => 0,
                    'apply_to'                      => null,
                    'position'                      => 0,
                    'is_used_for_promo_rules'       => 0,
                ];

                foreach (array_keys($columns) as $column) {
                    $data[$column] = $row[$column];
                }
            }

            $data = array_merge($defaultValues, $data);
            $this->eavSetup->updateAttribute(
                $this->getEntityTypeId(),
                $row['_entity_id'],
                $data,
                null,
                0
            );

            /* Add Attribute to group and family */
            if ($row['_attribute_set_id'] && $row['group']) {
                $attributeSetIds = explode(',', $row['_attribute_set_id']);

                if (is_numeric($row['group'])) {
                    $row['group'] = 'PIM' . $row['group'];
                }

                foreach ($attributeSetIds as $attributeSetId) {
                    if (is_numeric($attributeSetId)) {
                        /* Verify if the group already exists */
                        /** @var int $setId */
                        $setId = $this->eavSetup->getAttributeSetId($this->getEntityTypeId(), $attributeSetId);
                        /** @var int $groupId */
                        $groupId = $this->eavSetup->getSetup()->getTableRow(
                            'eav_attribute_group',
                            'attribute_group_name',
                            ucfirst($row['group']),
                            'attribute_group_id',
                            'attribute_set_id',
                            $setId
                        );

                        if ($groupId) {
                            /* The group already exists, update it */
                            /** @var string[] $dataGroup */
                            $dataGroup = [
                                'attribute_set_id' => $setId,
                                'attribute_group_name' => ucfirst($row['group']),
                            ];

                            $this->eavSetup->updateAttributeGroup(
                                $this->getEntityTypeId(),
                                $setId,
                                $groupId,
                                $dataGroup
                            );

                            $this->eavSetup->addAttributeToSet(
                                $this->getEntityTypeId(),
                                $attributeSetId,
                                $groupId,
                                $row['_entity_id']
                            );
                        } else {
                            /* The group doesn't exists, create it */
                            $this->eavSetup->addAttributeGroup(
                                $this->getEntityTypeId(),
                                $attributeSetId,
                                ucfirst($row['group'])
                            );

                            $this->eavSetup->addAttributeToSet(
                                $this->getEntityTypeId(),
                                $attributeSetId,
                                ucfirst($row['group']),
                                $row['_entity_id']
                            );
                        }
                    }
                }
            }

            /* Add store labels */
            /** @var array $stores */
            $stores = $this->storeHelper->getStores('lang');
            /**
             * @var string $lang
             * @var array $data
             */
            foreach ($stores as $lang => $data) {
                if (isset($row['labels-'.$lang])) {
                    /** @var array $store */
                    foreach ($data as $store) {
                        /** @var string $exists */
                        $exists = $connection->fetchOne(
                            $connection->select()->from($this->entitiesHelper->getTable('eav_attribute_label'))->where(
                                'attribute_id = ?',
                                $row['_entity_id']
                            )->where('store_id = ?', $store['store_id'])
                        );

                        if ($exists) {
                            /** @var array $values */
                            $values = [
                                'value' => $row['labels-'.$lang],
                            ];
                            /** @var array $where */
                            $where  = [
                                'attribute_id = ?' => $row['_entity_id'],
                                'store_id = ?'     => $store['store_id'],
                            ];

                            $connection->update(
                                $this->entitiesHelper->getTable('eav_attribute_label'),
                                $values,
                                $where
                            );
                        } else {
                            $values = [
                                'attribute_id' => $row['_entity_id'],
                                'store_id'     => $store['store_id'],
                                'value'        => $row['labels-'.$lang],
                            ];
                            $connection->insert($this->entitiesHelper->getTable('eav_attribute_label'), $values);
                        }
                    }
                }
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo;

use JustBetter\AkeneoBundle\Model\ResourceModel\Akeneo\CollectionFactory;
use JustBetter\AkeneoBundle\Model\Status;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Module\Manager;

class Grid extends Extended
{
    public function __construct(
        Context $context,
        Data $backendHelper,
        protected CollectionFactory $collectionFactory,
        protected Status $status,
        protected Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    protected function _prepareCollection(): Grid
    {
        $collection = $this->collectionFactory->create();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns(): Grid
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'import',
            [
                'header' => __('Type'),
                'index' => 'import',
                'type' => 'options',
                'options' => self::getOptionArray0(),
            ]
        );

        $this->addColumn(
            'code',
            [
                'header' => __('Code'),
                'index' => 'code',
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Magento Entity ID'),
                'index' => 'entity_id',
                'type' => 'int',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );

        $this->addExportType($this->getUrl('akeneomanager/*/exportCsv', ['_current' => true]), __('CSV'));
        $this->addExportType($this->getUrl('akeneomanager/*/exportExcel', ['_current' => true]), __('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction(): Grid
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('akeneo');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('akeneomanager/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('akeneomanager/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );

        return $this;
    }

    public function getGridUrl(): string
    {
        return $this->getUrl('akeneomanager/*/index', ['_current' => true]);
    }

    public function getRowUrl($row): string
    {
        return $this->getUrl('akeneomanager/*/edit', ['id' => $row->getId()]);
    }

    public static function getOptionArray0(): array
    {
        return [
            'family' => 'family',
            'attribute' => 'attribute',
            'category' => 'category',
            'product' => 'product',
            'option' => 'option',
        ];
    }

    public static function getValueArray0(): array
    {
        return array_map(
            fn($k, $v) => ['value' => $k, 'label' => $v],
            array_keys(self::getOptionArray0()),
            self::getOptionArray0()
        );
    }
}

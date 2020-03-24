<?php

namespace JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo;

use Magento\Backend\Helper\Data;
use Magento\Framework\Module\Manager;
use Magento\Backend\Block\Template\Context;
use JustBetter\AkeneoBundle\Model\Status;
use Magento\Backend\Block\Widget\Grid\Extended;
use JustBetter\AkeneoBundle\Model\AkeneoFactory;
use JustBetter\AkeneoBundle\Block\Adminhtml\Akeneo\Grid as GridOption;

class Grid extends Extended
{
    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var akeneoFactory
     */
    protected $_akeneoFactory;

    /**
     * @var Status
     */
    protected $_status;

    /**
     * @param Context         $context
     * @param Data            $backendHelper
     * @param AkeneoFactory   $AkeneoFactory
     * @param Status          $status
     * @param Manager         $moduleManager
     * @param array           $data
     *
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        AkeneoFactory $AkeneoFactory,
        Status $status,
        Manager $moduleManager,
        array $data = []
    ) {
        $this->_akeneoFactory = $AkeneoFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_akeneoFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header'           => __('ID'),
                'type'             => 'number',
                'index'            => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'import',
            [
                'header'  => __('Type'),
                'index'   => 'import',
                'type'    => 'options',
                'options' => GridOption::getOptionArray0(),
            ]
        );

        $this->addColumn(
            'code',
            [
                'header' => __('Code'),
                'index'  => 'code',
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Magento Entity ID'),
                'index'  => 'entity_id',
                'type'   => 'int',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created'),
                'index'  => 'created_at',
                'type'   => 'datetime',
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

    
    /**
     * @return $this
     */
    protected function _prepareMassaction()
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

        $statuses = $this->_status->getOptionArray();

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

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('akeneomanager/*/index', ['_current' => true]);
    }

    /**
     * @param \JustBetter\AkeneoBundle\Model\akeneo|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'akeneomanager/*/edit',
            ['id' => $row->getId()]
        );
    }
    
    public static function getOptionArray0()
    {
        $data_array = array();
        $data_array['family'] = 'family';
        $data_array['attribute'] = 'attribute';
        $data_array['category'] = 'category';
        $data_array['product'] = 'product';
        $data_array['option'] = 'option';

        return ($data_array);
    }

    public static function getValueArray0()
    {
        $data_array=array();
        foreach (GridOption::getOptionArray0() as $k=> $v) {
            $data_array[]=array('value'=>$k,'label'=>$v);
        }
        return($data_array);
    }
}

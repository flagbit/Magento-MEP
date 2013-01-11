<?php

class Flagbit_MEP_Block_Adminhtml_Attribute_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Class Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('attribute_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        //$this->setUseAjax(true);
    }

    /**
     * _prepareCollection
     *
     * Prepares the collection for the grid
     *
     * @return Flagbit_MEP_Block_Adminhtml_Profile_View_Grid Self.
     */
    protected function _prepareCollection()
    {
        /* @var $collection Flagbit_MEP_Model_Profil */
        $collection = Mage::getModel('mep/attribute_mapping')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * _prepareColumns
     *
     * Prepares the columns for the grid
     *
     * @return  Self.
     */
    protected function _prepareColumns()
    {


        $this->addColumn('id', array(
            'header' => Mage::helper('mep')->__('ID'),
            'align' => 'left',
            'index' => 'id',
            'width' => '50px'
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('mep')->__('Mapping Name'),
            'align' => 'left',
            'index' => 'name',
        ));
        $this->addColumn('attribute_code', array(
            'header' => Mage::helper('mep')->__('Attribute Code'),
            'align' => 'left',
            'index' => 'attribute_code',
        ));
        $this->addColumn('source_attribute_code', array(
            'header' => Mage::helper('mep')->__('Source Attribute Code'),
            'align' => 'left',
            'index' => 'source_attribute_code',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('adminhtml')->__('Action'),
            'width' => '100px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('adminhtml')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id',
                ),
                array(
                    'caption' => Mage::helper('adminhtml')->__('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                ),

            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        parent::_prepareColumns();
        return $this;
    }


    /**
     * Returns the row url
     *
     * @return string URL
     */
    public function getRowUrl($row)
    {
        $url = $this->getUrl('*/*/edit', array('id' => $row->getId()));
        return $url;
    }
}

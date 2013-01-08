<?php

class Flagbit_MEP_Block_Adminhtml_Shipping_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Class Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('profile_grid');
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
        $collection = Mage::getModel('mep/shipping')->getCollection();
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
            'header' => Mage::helper('mep')->__('Profile Name'),
            'align' => 'left',
            'index' => 'name',
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
     * _prepareMassaction
     *
     * Prepares the mass actions
     *
     * @return  Self.
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('mep')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('mep')->__('Are you sure?')
        ));


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

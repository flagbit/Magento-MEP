<?php

class Flagbit_MEP_Block_Adminhtml_Profil_View_GridMapping extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
     * Class Constructor
     * 
     * @return void
     */
	public function __construct()
	{
		parent::__construct();
		$this->setId('mapping_grid');
		$this->setUseAjax(true); // Using ajax grid is important
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        //$this->setDefaultFilter(array('in_products'=>1)); // By default we have added a filter for the rows, that in_products value to be 1
        $this->setSaveParametersInSession(false);  //Dont save paramters in session or else it creates problems
	}

	/**
     * _prepareCollection
     * 
     * Prepares the collection for the grid
     * 
     * @return Flagbit_MEP_Block_Adminhtml_Profil_View_Grid Self.
     */
	protected function _prepareCollection()
	{
        /* @var $collection Flagbit_MEP_Model_Profil */
		$collection = Mage::getModel('mep/mapping')->getCollection();
        $profil_id = $this->getProfile();
        if(!empty($profil_id)){
            $collection->addFieldToFilter('profile_id',array('eq'=> $profil_id));
        }

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
		));
        $this->addColumn('attribute_id', array(
			'header' => Mage::helper('mep')->__('Attribute ID'),
			'align' => 'left',
			'index' => 'attribute_id',
		));

        $this->addColumn('to_field', array(
            'header' => Mage::helper('mep')->__('To Field'),
            'align' => 'left',
            'index' => 'to_field',
        ));

        $this->addColumn('format', array(
            'header' => Mage::helper('mep')->__('Format'),
            'align' => 'left',
            'index' => 'format',
            'filter' => false,
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
             'label'=> Mage::helper('mep')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
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
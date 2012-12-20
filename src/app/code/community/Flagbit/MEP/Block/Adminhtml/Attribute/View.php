<?php

class Flagbit_MEP_Block_Adminhtml_Attribute_View
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Class Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->_blockGroup = 'mep';
        $this->_controller = 'adminhtml_Attribute_view';
        $this->_headerText = Mage::helper('mep')->__('Manage Attribute Mapping');
        parent::__construct();
    }
}
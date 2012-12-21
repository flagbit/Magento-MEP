<?php

class Flagbit_MEP_Block_Adminhtml_Shipping_View
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
        $this->_controller = 'adminhtml_shipping_view';
        $this->_headerText = Mage::helper('mep')->__('Manage Shipping Mapping');
        parent::__construct();
    }
}
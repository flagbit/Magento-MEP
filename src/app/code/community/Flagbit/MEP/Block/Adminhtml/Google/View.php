<?php

class Flagbit_MEP_Block_Adminhtml_Google_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Class Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->_blockGroup = 'mep';
        $this->_controller = 'adminhtml_google_view';
        $this->_mode = 'edit';
        $this->_headerText = Mage::helper('mep')->__('Manage Google Category Mapping');
        parent::__construct();
    }

    protected function _prepareLayout()
    {
        if ($this->_blockGroup && $this->_controller && $this->_mode) {
            $this->setChild('form', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_controller . '_' . $this->_mode . '_form'));
        }
        return parent::_prepareLayout();
    }
}
<?php

class Flagbit_MEP_Block_Adminhtml_Export_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->removeButton('back')
            ->removeButton('reset')
            ->removeButton('save');
    }

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'export_id';
        $this->_blockGroup = 'mep';
        $this->_controller = 'adminhtml_export';
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('mep')->__('Export');
    }
}

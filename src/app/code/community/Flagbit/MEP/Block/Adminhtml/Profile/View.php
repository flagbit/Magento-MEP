<?php

class Flagbit_MEP_Block_Adminhtml_Profile_View
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Config key for value of current version of MEP module
     */
    const CONFIG_KEY_MEP_VERSION = 'modules/Flagbit_MEP/version';

    /**
     * Class Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->_blockGroup = 'mep';
        $this->_controller = 'adminhtml_profile_view';
        $this->_headerText = Mage::helper('mep')->__('Manage MEP Profiles');
        parent::__construct();
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    public function _toHtml()
    {
        return parent::_toHtml() . $this->getVersionHtml();
    }

    /**
     * Returns HTML block that contains information about current version of the MEP module
     *
     * @return string HTML
     */
    public function getVersionHtml()
    {
        $version = strval(Mage::getConfig()->getNode(self::CONFIG_KEY_MEP_VERSION));

        return '<div class="mep-version">MEP Version ' . $version . '</div>';
    }
}
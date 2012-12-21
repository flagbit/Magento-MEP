<?php

class Flagbit_MEP_Block_Adminhtml_Shipping_Popup
    extends Mage_Core_Block_Template
{

    protected $_selectAttributeCodes = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function getProfileId()
    {
        return $this->getRequest()->getParam('profile_id', null);
    }

    public function getMapping()
    {
        return Mage::getModel('mep/shipping_attribute')->load($this->getRequest()->getParam('id'));
    }
}
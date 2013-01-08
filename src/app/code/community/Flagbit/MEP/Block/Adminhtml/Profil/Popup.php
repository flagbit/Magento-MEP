<?php

class Flagbit_MEP_Block_Adminhtml_Profil_Popup
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

    public function getShippingId()
    {
        return $this->getRequest()->getParam('shipping_id', null);
    }

    public function getMapping()
    {
        return Mage::getModel('mep/mapping')->load($this->getRequest()->getParam('id'));
    }

    public function getIsSelectedAttribute($attributeCode)
    {
        $result = false;
        if( !in_array($attributeCode, $this->_selectAttributeCodes)
            && in_array($attributeCode, $this->getMapping()->getAttributeCodeAsArray())){
            $this->_selectAttributeCodes[] = $attributeCode;
            $result = true;
        }
        return $result;
    }
}
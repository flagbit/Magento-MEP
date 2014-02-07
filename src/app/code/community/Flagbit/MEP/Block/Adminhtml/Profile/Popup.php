<?php

class Flagbit_MEP_Block_Adminhtml_Profile_Popup
    extends Mage_Core_Block_Template
{
    protected $_mapping;

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
        if (!$this->_mapping) {
            $this->_mapping = Mage::getModel('mep/mapping')->load($this->getRequest()->getParam('id'));
        }
        return $this->_mapping;
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

    public function getImageUrlType() {
        if ($this->getMapping()->getAttributeCode() == 'image_url') {
            return $this->getMapping()->getOption('image_url_type');
        }
        return null;
    }
}
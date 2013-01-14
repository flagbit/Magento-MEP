<?php

class Flagbit_MEP_Model_Shipping_Attribute extends Mage_Core_Model_Abstract
{
    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('mep/shipping_attribute');
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        //validate attribute_code
        $validatorAttrCode = new Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{1,254}$/'));
        if (!$validatorAttrCode->isValid($this->getAttributeCode())) {
            Mage::throwException(Mage::helper('mep')->__('Attribute code is invalid. Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'));
        }
        return parent::_beforeSave();
    }

}
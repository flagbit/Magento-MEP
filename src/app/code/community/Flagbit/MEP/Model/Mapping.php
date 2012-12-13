<?php

class Flagbit_MEP_Model_Mapping extends Mage_Core_Model_Abstract
{
    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('mep/mapping');
    }

    public function getToFieldNormalized()
    {
        return Mage::helper('mep')->normalizeVariableName($this->getToField());
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if(is_array($this->getAttributeCode())){
            $this->setAttributeCode(implode(',', $this->getAttributeCode()));
        }
        return parent::_beforeSave();
    }

    /**
     * Processing object after load data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        if(strpos($this->getAttributeCode(), ',') !== false){
            $this->setAttributeCode(explode(',', $this->getAttributeCode()));
        }
        return parent::_afterLoad();
    }

    public function getAttributeCodeAsArray()
    {
        $result = $this->getAttributeCode();
        if(!is_array($this->getAttributeCode())){
            if($this->getAttributeCode() === NULL){
                $result = array();
            }else{
                $result = array($this->getAttributeCode());
            }
        }
        return $result;
    }

}
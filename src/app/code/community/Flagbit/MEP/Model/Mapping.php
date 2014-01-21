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
        $this->setInheritance(Mage::app()->getRequest()->getParam('inheritance', 0));
        if ($this->getInheritance() == 0) {
            $this->setInheritanceType('');
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
        if(!$this->getId() && !$this->getPosition())
        {
            $this->setPosition($this->_getNextPosition());
        }
        if(strpos($this->getAttributeCode(), ',') !== false){
            $this->setAttributeCode(explode(',', $this->getAttributeCode()));
        }
        return parent::_afterLoad();
    }

    /**
     * get next Position
     */
    protected function _getNextPosition()
    {
        $position = 100;
        if($profileId = Mage::helper('mep')->getCurrentProfileData(true)){
            $mapping = $this->getCollection();
            $mapping->addFieldToFilter('profile_id', array('eq' => $profileId));
            $mapping->setOrder('position', 'DESC');
            $position = $mapping->getFirstItem()->getPosition() + 1;
        }
        return $position;
    }

    /**
     * get Attribute Code as Array
     * This is used for multiple Attributes in one Mapping
     *
     * @return array
     */
    public function getAttributeCodeAsArray()
    {
        $result = $this->getAttributeCode();
        if(!is_array($this->getAttributeCode())){
            if($this->getAttributeCode() === NULL){
                $result = array();
            }else{
                if(strpos($this->getAttributeCode(), ',') === false){
                    $result = array($this->getAttributeCode());
                }else{
                    $result = explode(',', $this->getAttributeCode());
                }
            }
        }
        return $result;
    }

}
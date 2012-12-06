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
}
<?php

class Flagbit_MEP_Model_Resource_Shipping extends Mage_Core_Model_Resource
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {

        $this->_init('mep/shipping', 'id');
    }
}
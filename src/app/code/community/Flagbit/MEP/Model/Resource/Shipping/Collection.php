<?php

class Flagbit_MEP_Model_Resource_Shipping_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('mep/shipping');
    }

}
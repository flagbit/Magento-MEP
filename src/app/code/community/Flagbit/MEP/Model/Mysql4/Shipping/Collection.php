<?php

class Flagbit_MEP_Model_Mysql4_Shipping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
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
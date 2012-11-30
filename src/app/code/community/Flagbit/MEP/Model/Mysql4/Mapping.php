<?php

class Flagbit_MEP_Model_Mysql4_Mapping extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Constructor
     *
     */
    protected function _construct()
    {

        $this->_init('mep/mapping', 'id');
    }
}
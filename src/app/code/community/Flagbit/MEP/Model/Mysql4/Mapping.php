<?php

class Flagbit_MEP_Model_Mysql4_Mapping extends Mage_Core_Model_Mysql4_Abstract
{

    protected $_uniqueFields = array(
        array('field' => array('to_field', 'profile_id'), 'title' => 'Attribute Code')
    );


    /**
     * Constructor
     *
     */
    protected function _construct()
    {

        $this->_init('mep/mapping', 'id');
    }
}
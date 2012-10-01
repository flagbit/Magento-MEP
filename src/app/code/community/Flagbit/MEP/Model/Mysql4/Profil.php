<?php

class Flagbit_MEP_Model_Mysql4_Profil extends Mage_Core_Model_Mysql4_Abstract {

    /**
     * Constructor
     *
     */
    protected function _construct() {

        $this->_init('mep/profil', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $now =Varien_Date::now(false);
        $object->setUpdatedAt($now);
    }

}
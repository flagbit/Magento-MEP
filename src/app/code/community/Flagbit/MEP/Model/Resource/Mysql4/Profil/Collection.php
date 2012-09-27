<?php

class Flagbit_MEP_Model_Resource_Mysql4_Profil_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    /**
     * Constructor
     *
     */
    protected function _construct() {
        $this->_init('profil/profil');
    }


    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Flagbit_CronCli_Model_Resource_Mysql4_Indexer_Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        $this->getSelect()->order($attribute. ' ' . $dir);
        return $this;
    }

    /**
     * @param $limit
     */
    public function setLimit($limit){
        $this->getSelect()->limitPage(0,10);
        return $this;
    }



}
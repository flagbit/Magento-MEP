<?php

class Flagbit_MEP_Model_GoogleMapping extends Mage_Core_Model_Abstract {

    public function __construct() {
        $this->_init('mep/googleMapping');
    }

    public function loadByCategoryAndStore($categoryId, $storeId){
        $collection = $this->getCollection()
            ->addFieldToFilter('category_id', $categoryId)
            ->addFieldToFilter('store_id', $storeId);
        $mapping =  $collection->getFirstItem();
        return $mapping;
    }
}
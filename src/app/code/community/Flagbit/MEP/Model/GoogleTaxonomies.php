<?php

class Flagbit_MEP_Model_GoogleTaxonomies extends Mage_Core_Model_Abstract {

    public function __construct() {
        $this->_init('mep/googleTaxonomies');
    }

    public function getTaxonomiesForParent($parentId)
    {
        return $this->getCollection()->addFieldToFilter('parent_id', $parentId);
    }
}
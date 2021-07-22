<?php

class Flagbit_MEP_Model_GoogleTaxonomies extends Mage_Core_Model_Abstract {

    public function __construct() {
        $this->_init('mep/googleTaxonomies');
    }

    public function getTaxonomiesForParent($parentId, $language = null)
    {
        if(is_null($language)){
            return $this->getCollection()->addFieldToFilter('parent_id', $parentId);
        } else {
            return $this->getCollection()
                ->addFieldToFilter('parent_id', $parentId)
                ->addFieldToFilter('locale', $language)
                ;
        }
    }
}

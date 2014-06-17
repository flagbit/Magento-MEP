<?php

class Flagbit_MEP_Model_Mysql4_GoogleTaxonomies extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Constructor
     *
     */
    public function _construct() {
        $this->_init('mep/google_taxonomies', 'taxonomy_id');
    }
}
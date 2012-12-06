<?php

class Flagbit_MEP_Model_Mysql4_Mapping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('mep/mapping');
    }

    /**
     * add Attribute Settings
     *
     * @return Flagbit_MEP_Model_Mysql4_Mapping_Collection
     */
    public function addAttributeSettings()
    {
        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();

        $this->getSelect()->joinLeft(
            array('set_table' => $this->getTable('eav/attribute')),
            $this->getResource()->getReadConnection()->quoteInto('main_table.attribute_code = set_table.attribute_code' .
            ' AND set_table.entity_type_id = ?', $entityTypeId),
            array('backend_type', 'frontend_input')
        );
        return $this;
    }

}
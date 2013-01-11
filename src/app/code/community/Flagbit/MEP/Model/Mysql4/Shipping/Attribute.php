<?php

class Flagbit_MEP_Model_Mysql4_Shipping_Attribute extends Mage_Core_Model_Mysql4_Abstract
{

    protected $_uniqueFields = array(
        array('field' => array('attribute_code', 'profile_id'), 'title' => 'Attribute Code')
    );

    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('mep/shipping_attribute', 'id');
    }

    /**
     * Check for unique values existence
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     * @throws Mage_Core_Exception
     */
    protected function _checkUnique(Mage_Core_Model_Abstract $object)
    {
        $data = new Varien_Object($this->_prepareDataForSave($object));
        $select = $this->_getWriteAdapter()->select()
            ->from($this->getTable('eav/attribute'))
            ->where('attribute_code' . '=?', trim($data->getData('attribute_code')));

        $select2 = $this->_getWriteAdapter()->select()
            ->from($this->getTable('mep/attribute_mapping'))
            ->where('attribute_code' . '=?', trim($data->getData('attribute_code')));

        if ($this->_getWriteAdapter()->fetchRow($select) || $this->_getWriteAdapter()->fetchRow($select2)) {
            Mage::throwException(Mage::helper('core')->__('There is already a Magento Attribute with the Code "%s".', $data->getData('attribute_code')));
        }

        return parent::_checkUnique($object);
    }
}
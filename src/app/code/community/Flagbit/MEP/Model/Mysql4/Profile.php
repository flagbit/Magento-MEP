<?php

class Flagbit_MEP_Model_Mysql4_Profile extends Mage_Core_Model_Mysql4_Abstract
{

    protected $_serializedAttr = array('conditions_serialized', 'settings');
    /**
     * Constructor
     *
     */
    protected function _construct()
    {

        $this->_init('mep/profile', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        foreach ($this->_serializedAttr as $attrCode) {
            if (is_array($object->getData($attrCode))) {
                $object->setData($attrCode, serialize($object->getData($attrCode)));
            }
        }

        $now = Varien_Date::now(false);
        $object->setUpdatedAt($now);
    }


    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        foreach ($this->_serializedAttr as $attrCode) {
            if (!is_array($object->getData($attrCode))) {
                $object->setData($attrCode, unserialize($object->getData($attrCode)));
            }
        }
        return $this;
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
            ->from($this->getTable('mep/profile'))
            ->where('filename' . '=?', trim($data->getData('filename')))
            ->where('filepath' . '=?', trim($data->getData('filepath')))
            ->where('id' . '!=?', trim($data->getData('id')));


        if ($this->_getWriteAdapter()->fetchRow($select)) {
            Mage::throwException(Mage::helper('core')->__('There is already a Magento Profile with the Same Export Filename "%s" and Path ', $data->getData('filename')));
        }

        return parent::_checkUnique($object);
    }

    public function  saveField($field, $value, $profileId) {
        $this->_getWriteAdapter()->update($this->getMainTable(), array($field => $value), 'id = ' . $profileId);
    }
}
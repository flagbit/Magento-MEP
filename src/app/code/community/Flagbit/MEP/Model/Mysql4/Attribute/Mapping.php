<?php

class Flagbit_MEP_Model_Mysql4_Attribute_Mapping extends Mage_Core_Model_Mysql4_Abstract
{

    protected $_uniqueFields = array(
        array('field' => 'attribute_code', 'title' => 'Attribute Code')
    );

    protected $_optionValues = null;

    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('mep/attribute_mapping', 'id');
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

        if($this->_getWriteAdapter()->fetchRow($select)){
            Mage::throwException(Mage::helper('core')->__('There is already a Magento Attribute with the Code "%s".', $data->getData('attribute_code')));
        }

        return parent::_checkUnique($object);
    }

    /**
     * Save additional attribute data after save attribute
     *
     * @param Mage_Eav_Model_Entity_Attribute $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $this->_saveOption($object);

        return parent::_afterSave($object);
    }

    /**
     * get Option Value
     *
     * @param Mage_Core_Model_Abstract $object
     * @param $optionId
     * @param $storeId
     * @return null
     */
    public function getOptionValue(Mage_Core_Model_Abstract $object, $optionId, $storeId, $removeEmptyValues = true)
    {
        $result = null;
        if($this->_optionValues === null || !isset($this->_optionValues[$object->getId()])){
            $conn = $this->getReadConnection();
            $select = $conn->select()->from($this->getTable('mep/attribute_mapping_option'), array('option_id', 'value'))
                            ->where('parent_id=?', $object->getId())
                            ->where('store_id=?', $storeId);
            $this->_optionValues[$object->getId()] = $conn->fetchPairs($select);
        }
        if(is_array($optionId)){
            $result = array();
            foreach($optionId as $id){
                $value = $this->getOptionValue($object, $id, $storeId);
                if($removeEmptyValues === true && empty($value)){
                    continue;
                }
                $result[] = $value;
            }
        }else{
            $result = empty($this->_optionValues[$object->getId()][$optionId]) ? null : $this->_optionValues[$object->getId()][$optionId];
        }
        return $result;
    }

    /**
     *  Save attribute options
     *
     * @param Mage_Eav_Model_Entity_Attribute $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _saveOption(Mage_Core_Model_Abstract $object)
    {
        $option = $object->getOption();
        if (is_array($option)) {
            $adapter            = $this->_getWriteAdapter();
            $optionTable        = $this->getTable('mep/attribute_mapping_option');

            $stores = Mage::app()->getStores(false);
            if (isset($option['value'])) {
                foreach ($option['value'] as $optionId => $values) {
                    $intOptionId = (int) $optionId;
                    $adapter->delete($optionTable, array('option_id =?' => $intOptionId, 'parent_id=?' => $object->getId() ));
                    foreach ($stores as $store) {
                        if (isset($values[$store->getId()])
                            && (!empty($values[$store->getId()]))
                        ) {
                            $data = array(
                                'parent_id' => $object->getId(),
                                'option_id' => $intOptionId,
                                'store_id'  => $store->getId(),
                                'value'     => $values[$store->getId()],
                            );
                            $adapter->insert($optionTable, $data);
                        }
                    }
                }
            }
        }

        return $this;
    }



}
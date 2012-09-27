<?php

class Flagbit_PSExport_Model_Resource_Eav_Mysql4_Product_Action extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Action
{

	
    /**
     * Update attribute values for entity list per store
     *
     * @param array $entityIds
     * @param array $attrData
     * @param int $storeId
     * @return Mage_Catalog_Model_Product_Action
     */
    public function updateAttributes($entityIds, $attrData, $storeId)
    {
        $object = new Varien_Object();
        $object->setIdFieldName('entity_id')
            ->setStoreId($storeId);

        $this->_getWriteAdapter()->beginTransaction();
        try {
            foreach ($attrData as $attrCode => $value) {
                $attribute = $this->getAttribute($attrCode);
                if (!$attribute->getAttributeId()) {
                    continue;
                }

                $i = 0;
                foreach ($entityIds as $entityId) {
                    $object->setId($entityId);
                    // collect data for save    	
                    
                    // handle PSExport attributes
                    if(substr($attrCode, 0, 8) == 'psexport'){
                    	$this->_saveAttributeValue(
                    				$object, 
                    				$attribute, 
                    				Mage::getSingleton('flagbit_psexport/entity_attribute_backend_'.substr($attrCode, 9))
                    					->filterData($value, $entityId, $storeId)
                    				);
                    }else{  				
                    	$this->_saveAttributeValue($object, $attribute, $value);
                    }
                    // save collected data every 1000 rows
                    if ($i % 1000 == 0) {
                        $this->_processAttributeValues();
                    }
                }
            }
            $this->_processAttributeValues();
            $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }

        return $this;
    }
}

<?php 
class Flagbit_PSExport_Model_Entity_Attribute_Backend_Abstract extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
	
	public function filterData($data, $productId, $storeId){

		return Mage::getSingleton('flagbit_psexport/filter')
				->setProductId($productId)
				->setStoreId($storeId)
				->filter($data);
		
	}

    public function beforeSave($object)
    {
    	parent::beforeSave($object);

        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->hasData($attrCode)) {
        	$object->setData($attrCode, 
        		$this->filterData(
        			$object->getData($attrCode), 
        			$object->getId(), 
        			$object->getStoreId()
        			)
        		);
        }
    }	
	
}

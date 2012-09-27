<?php

class Flagbit_PSExport_Model_Filter extends Varien_Filter_Template {

	protected $_productId = null;
	protected $_storeId = null;
	protected $_products = array();
	protected $_attributeKeys = array();
	protected $_inludeParameterCache = array();

    /**
     * Filter the string as template.
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {

        if(preg_match_all(self::CONSTRUCTION_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
        	
        	foreach($constructions as $index=>$construction){
        	    $callback = array($this, $construction[1].'PreDirective');
                if(!is_callable($callback)) {
                    continue;
                }
                call_user_func($callback, $construction);        		
        	}
        	       	
            foreach($constructions as $index=>$construction) {
                $replacedValue = '';
                $callback = array($this, $construction[1].'Directive');
                if(!is_callable($callback)) {
                    continue;
                }
                try {
					$replacedValue = call_user_func($callback, $construction);
                } catch (Exception $e) {
                	throw $e;
                }
                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }
        return $value;
    }	
	
	public function setProductId($Id){

		$this->_productId = $Id;
		return $this;
	}

	public function setStoreId($Id){
		
		$this->_storeId = $Id;
		return $this;
	}	
	
	protected function _getProduct($id){
		
		if(!isset($this->_products[$this->_storeId])){
			
			/*@var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
	        $collection = Mage::getModel('catalog/product')->getCollection()
	            ->addAttributeToSelect('sku')
	            ->addAttributeToSelect('name');
         
	        foreach($this->_attributeKeys['product'] as $attributeKey){
	        	$collection->addAttributeToSelect($attributeKey);
	        }    

	        if ($this->_storeId) {

	            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
	            $collection->addStoreFilter($this->_storeId);
	            $collection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $adminStore);

	        }
	        else {
	            $collection->addAttributeToSelect('price');
	            $collection->addAttributeToSelect('status');
	            $collection->addAttributeToSelect('visibility');
        	}
			$this->_products[$this->_storeId] = $collection;
			
		}
		return $this->_products[$this->_storeId]->getItemById($id);
	}
	
	public function getPreDirective($construction){
		
		$params = $this->_getIncludeParameters($construction[2]);
		
		if(empty($params['type'])){
			$params['type'] = 'product';
		}
				
		if(!isset($this->_attributeKeys[$params['type']]) 
			|| !in_array($params['key'], $this->_attributeKeys[$params['type']])){
				
			$this->_attributeKeys[$params['type']][] = $params['key'];
		}
	}
	
    /**
     * Return associative array of include construction.
     *
     * @param string $value raw parameters
     * @return array
     */
    protected function _getIncludeParameters($value)
    {    	
    	if(!isset($this->_inludeParameterCache[$value])){
    		$this->_inludeParameterCache[$value] = parent::_getIncludeParameters($value);
    	}
    	
        return $this->_inludeParameterCache[$value];
    }	
	
	
	public function getDirective($construction){
		$params = $this->_getIncludeParameters($construction[2]);
		
		if(empty($params['type'])){
			$params['type'] = 'product';
		}

		switch($params['type']){
			
			case 'product':
				$value = $this->_getProduct($this->_productId)->getData($params['key']);
				if($attribute = $this->_getAttribute($params['key'], $this->_getProduct($this->_productId))){
					if ($attribute->usesSource()) {
                    	$option = $attribute->getSource()->getOptionText($value);
                    	if(is_array($option)){
                    		$value = join(', ', $option);
                    	}else{
                    		$value = $option;
                    	}
					}
				}
				return $value;
				break;
					
		}
	}
	
    /**
     * Retrieve eav entity attribute model
     *
     * @param string $code
     * @return Mage_Eav_Model_Entity_Attribute
     */
    protected function _getAttribute($code, $product)
    {
        if (!isset($this->_attributes[$code])) {
            $this->_attributes[$code] = $product->getResource()->getAttribute($code);
        }
        return $this->_attributes[$code];
    }

}

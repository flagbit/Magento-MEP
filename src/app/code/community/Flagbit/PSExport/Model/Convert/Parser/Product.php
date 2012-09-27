<?php

class Flagbit_PSExport_Model_Convert_Parser_Product extends Mage_Catalog_Model_Convert_Parser_Product {
	
    protected $_parentProductModel;	
	
    /**
     * Retrieve accessible external product attributes
     *
     * @return array
     */
    public function getExternalAttributes()
    {
        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();
        $attributes = $this->_externalFields;

		$collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load();
            
		foreach($collection as $attributeSet){
		
			$attributes[preg_replace('/([^A-Za-z_-]*)/', '', $attributeSet->getAttributeSetName())] = $this->getAttributesBySet($attributeSet->getAttributeSetId());	
		}

        foreach ($this->_inventoryFields as $field) {
            $attributes[$field] = $field;
        }

        // added for url mapping
        $attributes['url'] = 'url';
        $attributes['image_url'] = 'image_url';
        $attributes['gross_price'] = 'gross_price';
        $attributes['fixed_value_format'] = 'fixed_value_format';
        
        $attributes['psexport_description'] = 'psexport_description';
        $attributes['psexport_name'] = 'psexport_name';

        return $attributes;
    }

    /**
     * Retrieve Attribute Set Group Tree as JSON format
     *
     * @return string
     */
    public function getAttributesBySet($setId)
    {
        $items = array();

        /* @var $groups Mage_Eav_Model_Mysql4_Entity_Attribute_Group_Collection */
        $groups = Mage::getModel('eav/entity_attribute_group')
            ->getResourceCollection()
            ->setAttributeSetFilter($setId)
            ->load();

        /* @var $node Mage_Eav_Model_Entity_Attribute_Group */
        foreach ($groups as $node) {

            $nodeChildren = Mage::getResourceModel('catalog/product_attribute_collection')
                ->setAttributeGroupFilter($node->getId())
                ->addVisibleFilter()
                ->checkConfigurableProducts();
                
            $nodeChildren->getSelect()->where('main_table.is_user_defined = ?', 1);    
			
			foreach($nodeChildren as $child ){
				
				if (in_array($child->getAttributeCode(), $this->_internalFields) || $child->getFrontendInput() == 'hidden') {
                	continue;
            	}				
				
            	$items[$child->getAttributeCode()] = $child->getAttributeCode();
			}
        }

        return $items;
    }

    protected function _getParentProductId(Mage_Catalog_Model_Product $childProduct){
    	
    	$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
            ->getParentIdsByChild($childProduct->getId());
        if(is_array($parentIds) && count($parentIds)){
			return $parentIds[0];
        }     	
    	return null;
    }
    
    /**
     * Retrieve product model cache
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProductModel($parent = false)
    {	
    	if($parent === true){
		
    		if (is_null($this->_parentProductModel)) {
   			
	        	$parentProductModel = Mage::getModel('catalog/product');
	        	$this->_parentProductModel = Mage::objects()->save($parentProductModel, 'parent_{hash}');
    		}
    		return Mage::objects()->load($this->_parentProductModel);       		
    	}
    	   	
        return parent::getProductModel();
    }    
    
    /**
     * Unparse (prepare data) loaded products
     *
     * @return Mage_Catalog_Model_Convert_Parser_Product
     */
    public function unparse()
    {
        $entityIds = $this->getData();
        $imageHelper = Mage::helper('catalog/image');

        foreach ($entityIds as $i => $entityId) {
            $product = $this->getProductModel()
                ->reset()
                ->setStoreId($this->getStoreId())
                ->load($entityId);

			$parentId = $this->_getParentProductId($product);
			
        	$parentProduct = $this->getProductModel(true)
                ->reset()
                ->setStoreId($this->getStoreId())
                ->load($parentId); 
                
			if($parentProduct->getId()){
				$this->setProductTypeInstance($parentProduct);
			} 
			
            $this->setProductTypeInstance($product);
            /* @var $product Mage_Catalog_Model_Product */

            $position = Mage::helper('catalog')->__('Line %d, SKU: %s', ($i+1), $product->getSku());
            $this->setPosition($position);

            $row = array(
                'store'         => $this->getStore()->getCode(),
                'websites'      => '',
                'attribute_set' => $this->getAttributeSetName($product->getEntityTypeId(), $product->getAttributeSetId()),
                'type'          => $product->getTypeId(),
                'category_ids'  => join(',', $product->getCategoryIds())
            );
            
            if ($this->getStore()->getCode() == Mage_Core_Model_Store::ADMIN_CODE) {
                $websiteCodes = array();
                foreach ($product->getWebsiteIds() as $websiteId) {
                    $websiteCode = Mage::app()->getWebsite($websiteId)->getCode();
                    $websiteCodes[$websiteCode] = $websiteCode;
                }
                $row['websites'] = join(',', $websiteCodes);
            }
            else {
                $row['websites'] = $this->getStore()->getWebsite()->getCode();
                if ($this->getVar('url_field')) {
                	$urlParams = array('_nosid' => true);
                	if ($this->getVar('tracking_param')) {
                		parse_str ($this->getVar('tracking_param'), $urlParams['_query']);
                	}
                    $row['url'] = $this->getUrl($parentProduct->getId() ? $parentProduct : $product, $urlParams);
                }
            }

			if ($product->getImage() != 'no_selection' && $product->getImage()) { 
            	$row['image_url'] = (string) $imageHelper->init($product, 'image'); 
			}elseif ($parentProduct->getImage() != 'no_selection' && $parentProduct->getImage()) {				 
            	$row['image_url'] = (string) $imageHelper->init($parentProduct, 'image');             	
			}else{
				$row['image_url'] = '';
			}
            
			$row['gross_price'] = $product->getFinalPrice();		
			$row['image_url'] = str_replace('https://', 'http://', $row['image_url']);
			
            foreach ($product->getData() as $field => $value) {
            	
                if (in_array($field, $this->_systemFields) || is_object($value)) {
                    continue;
                }

                $attribute = $this->getAttribute($field);
                if (!$attribute) {
                    continue;
                }
                $attribute->setStoreId($this->getStoreId());

                if ($attribute->usesSource()) {
                    $option = $attribute->getSource()->getOptionText($value);
                                        
                    if ($value && empty($option)) {
                        $message = Mage::helper('catalog')->__("Invalid option ID specified for %s (%s), skipping the record.", $field, $value);
                        $this->addException($message, Mage_Dataflow_Model_Convert_Exception::ERROR);
                        continue;
                    }
                    if (is_array($option)) {
                        $value = join(self::MULTI_DELIMITER, $option);
                    } else {
                        $value = $option;
                    }
                    unset($option);
                }
                elseif (is_array($value)) {
                    continue;
                }

     			$row[$field] = $value;
            }

            if ($stockItem = $product->getStockItem()) {
                foreach ($stockItem->getData() as $field => $value) {
                    if (in_array($field, $this->_systemFields) || is_object($value)) {
                        continue;
                    }
                    $row[$field] = $value;
                }
            }

            foreach ($this->_imageFields as $field) {
                if (isset($row[$field]) && $row[$field] == 'no_selection') {
                    $row[$field] = null;
                }
            }

			if(!isset($row['psexport_name'])){
				$row['psexport_name'] = $row['name'];
			}            
			if(!isset($row['psexport_description'])){
				$row['psexport_description'] = $row['description'];
			} 
			            
            $batchExport = $this->getBatchExportModel()
                ->setId(null)
                ->setBatchId($this->getBatchModel()->getId())
                ->setBatchData($row)
                ->setStatus(1)
                ->save();
        }

        return $this;
    }	

    
    
    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $params
     * @return string
     */
    public function getUrl(Mage_Catalog_Model_Product $product, $params = array())
    {
	    $routePath      = '';
        $routeParams    = $params;
        $routeParams['_secure'] = false;
     
        $storeId    = $product->getStoreId();
        if (isset($params['_ignore_category'])) {
            unset($params['_ignore_category']);
            $categoryId = null;
        } else {
            $categoryId = $product->getCategoryId() && !$product->getDoNotUseCategoryId()
                ? $product->getCategoryId() : null;
        }

        if ($product->hasUrlDataObject()) {
            $requestPath = $product->getUrlDataObject()->getUrlRewrite();
            $routeParams['_store'] = $product->getUrlDataObject()->getStoreId();
        }
        else {
            $requestPath = $product->getRequestPath();
            if (empty($requestPath)) {
                $idPath = sprintf('product/%d', $product->getEntityId());
                if ($categoryId) {
                    $idPath = sprintf('%s/%d', $idPath, $categoryId);
                }
                $rewrite = $product->getUrlModel()->getUrlRewrite();
                $rewrite->setStoreId($storeId)
                    ->loadByIdPath($idPath);
                if ($rewrite->getId()) {
                    $requestPath = $rewrite->getRequestPath();
                    $product->setRequestPath($requestPath);
                }
            }
        }

        if (isset($routeParams['_store'])) {
            $storeId = Mage::app()->getStore($routeParams['_store'])->getId();
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        }
        else {
            $routePath = 'catalog/product/view';
            $routeParams['id']  = $product->getId();
            $routeParams['s']   = $product->getUrlKey();
            if ($categoryId) {
                $routeParams['category'] = $categoryId;
            }
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = array();
        }
        
        return $product->getUrlModel()->getUrlInstance()->setStore($storeId)
            ->getUrl($routePath, $routeParams);
    }    
    
}
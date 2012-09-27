<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Flagbit_PSExport_Model_Convert_Adapter_Product
    extends Mage_Catalog_Model_Convert_Adapter_Product
{

	/**
	 * 
	 * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	 */
	protected $_collection = null;
	
    /**
     * Load product collection Id(s)
     *
     */
    public function load()
    {

        $filters = $this->_parseVars();

        if (!empty($filters['categories'])) {
        	
        	$categories = explode(',', $filters['categories']);
        	foreach($categories as $key => $value){
        		if(empty($value)){
        			unset($categories[$key]);
        		}
        	}
			

            $qtyAttr = array();
            $qtyAttr['alias']       = 'category_ids';
            $qtyAttr['attribute']   = 'catalog/category_product_index';
            $qtyAttr['field']       = 'category_id';
            $qtyAttr['bind']        = 'product_id=entity_id';
            $qtyAttr['cond'] = null;
            $qtyAttr['cond']        = "/*{{table}}.visibility > 1 AND*/ {{table}}.store_id = ".$this->getStoreId();
            $qtyAttr['joinType']    = 'left';
			
			if (! ($entityType = $this->getVar ( 'entity_type' )) || ! (Mage::getResourceSingleton ( $entityType ) instanceof Mage_Eav_Model_Entity_Interface)) {
				$this->addException ( Mage::helper ( 'eav' )->__ ( 'Invalid entity specified' ), Varien_Convert_Exception::FATAL );
			}
			
			$collection = $this->_getCollectionForLoad ( $entityType );
           
			
			$collection->getSelect()->where('`_table_category_ids`.`category_id` IN('.implode(',', array_unique($categories)).')');
            
            $this->setJoinField($qtyAttr);            

        }
         
		$load = parent::load();

		return $load;
    }
    
    /**
     * Retrieve not loaded collection
     *
     * @param string $entityType
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function _getCollectionForLoad($entityType)
    {
    	if(!($this->_collection instanceof Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection)){
    		
	        $this->_collection = Mage::getResourceModel($entityType.'_collection')			
	            ->setStoreId($this->getStoreId())
	            ->addStoreFilter($this->getStoreId());
            $this->_collection->getSelect()->distinct(true);
    	}
        return $this->_collection;
    }
 
    

}

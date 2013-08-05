<?php

class Flagbit_MEP_Block_Adminhtml_Attribute_View_Edit_Options extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Options_Abstract
{

    protected $_attribute = false;

    public function __construct()
    {
        #parent::__construct();
        switch($this->getAttributeMappingObject()->getSourceAttributeCode()){
            case 'category':
                $this->setTemplate('mep/attribute/mapping/categories.phtml');
                break;

            default:
                $this->setTemplate('mep/attribute/mapping/options.phtml');
                break;
        }
    }

    /**
     * Retrieve attribute option values if attribute input type select or multiselect
     *
     * @return array
     */
    public function getOptionValues()
    {
        $values = $this->getData('option_values');
        if (is_null($values)) {
            $values = array();
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($this->getAttributeObject()->getId())
                ->setPositionOrder('desc', true)
                ->load();

            foreach ($optionCollection as $option) {
                $value = array();

                $value['id'] = $option->getId();
                $value['sort_order'] = $option->getSortOrder();
                foreach ($this->getStores() as $store) {
                    $storeValues = $this->getStoreOptionValues($store->getId());
                    if (isset($storeValues[$option->getId()])) {
                        $value['store'.$store->getId()] = htmlspecialchars($storeValues[$option->getId()]);
                    }
                    else {
                        $value['store'.$store->getId()] = '';
                    }
                }
                $values[] = new Varien_Object($value);
            }
            $this->setData('option_values', $values);
        }

        return $values;
    }


    /**
     *
     *
     * @return Mage_Adminhtml_Block_Catalog_Category_Tree
     */
    protected function getCategories()
    {
        $values = $this->getData('option_values');
        if (is_null($values)) {

            $values = array();
            /* @var $categoryCollection Mage_Catalog_Model_Resource_Category_Collection */
            $categoryCollection = Mage::getResourceModel('catalog/category_collection')
                                    ->addAttributeToSelect('name')
                                    ->addAttributeToSelect('is_active')
                                    ->addFieldToFilter('is_active', '1')
                                    ->setOrder('position', 'asc')
                                    ->load();


            /* @var $category Mage_Catalog_Model_Category */
            foreach ($categoryCollection as $category) {
                $value = array();
                $value['id'] = $category->getId();
                $value['parent_id'] = $category->getParentId();
                if(!$category->getName()){
                    continue;
                }
                foreach ($this->getStores() as $store) {
                    if($store->getId()){
                        $storeValues = $this->getStoreOptionValues($store->getId());
                    }else{
                        $storeValues[$category->getId()] = $category->getName();
                    }
                    if (isset($storeValues[$category->getId()])) {
                        $value[$store->getId()] = htmlspecialchars($storeValues[$category->getId()]);
                    }
                    else {
                        $value[$store->getId()] = '';
                    }
                }
                $values[] = new Varien_Object($value);
            }
            $this->setData('option_values', $values);
        }

        return $values;
    }



    /**
     * Retrieve attribute option values for given store id
     *
     * @param integer $storeId
     * @return array
     */
    public function getStoreOptionValues($storeId)
    {
        if($storeId){
            $values = $this->getData('store_option_values_'.$storeId);
            if (is_null($values)) {
                $values = array();
                /* @var $valuesCollection Flagbit_MEP_Model_Mysql4_Attribute_Mapping_Option_Collection */
                $valuesCollection = Mage::getResourceModel('mep/attribute_mapping_option_collection')
                    ->addFieldToFilter('parent_id', $this->getAttributeMappingObject()->getId())
                    ->setStoreFilter($storeId, false)
                    ->load();

                foreach ($valuesCollection as $item) {
                    $values[$item->getOptionId()] = $item->getValue();
                }
                $this->setData('store_option_values_'.$storeId, $values);
            }
        }else{
            $values = parent::getStoreOptionValues($storeId);
        }
        return $values;
    }

    /**
     * @return Flagbit_MEP_Model_Attribute_Mapping|null
     */
    public function getAttributeMappingObject()
    {
        $object = null;
        if(Mage::registry('mep_attribute_mapping') instanceof Flagbit_MEP_Model_Attribute_Mapping){
            $object = Mage::registry('mep_attribute_mapping');
        }
        return $object;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttributeObject()
    {
        if($this->_attribute === false){
            $this->_attribute = null;
            if($this->getAttributeMappingObject() !== null){

                switch($this->getAttributeMappingObject()->getSourceAttributeCode()){

                    case 'category':
                        /* @var $attribute Mage_Eav_Model_Entity_Attribute */
                        $attribute = Mage::getModel('eav/entity_attribute');
                        $attribute->loadByCode(Mage::getModel('catalog/category')->getResource()->getTypeId(), 'name');
                        $this->_attribute = $attribute;
                        break;

                    default:
                        /* @var $attribute Mage_Eav_Model_Entity_Attribute */
                        $attribute = Mage::getModel('eav/entity_attribute');
                        $attribute->loadByCode(Mage::getModel('catalog/product')->getResource()->getTypeId(), $this->getAttributeMappingObject()->getSourceAttributeCode());
                        $this->_attribute = $attribute;
                }
            }
        }
        return $this->_attribute;
    }
}

<?php

class   Flagbit_MEP_Block_Adminhtml_Google_View_Edit_CategoriesList extends Mage_Adminhtml_Block_Abstract
{
    /**
     * @var Varien_Data_Tree_Node_Collection
     */
    protected $_tree;

    protected $_rootCategory;

    public function getCategoriesTree()
    {
        $storeId = Mage::registry('category_store_id');
        if ($storeId && empty($this->_tree))
        {
            $parent = Mage::app()->getStore($storeId)->getRootCategoryId();
            $this->_rootCategory = Mage::getModel('catalog/category')->load($parent);
            $recursionLevel  = max(0, (int) Mage::app()->getStore($storeId)->getConfig('catalog/navigation/max_depth'));
            $this->_tree = Mage::getModel('catalog/category')->getCategories($parent, $recursionLevel);
        }
        return $this->_tree;
    }

    public function getCategoriesTreeHtml()
    {
        $categoriesTree = $this->getCategoriesTree();
        $html = '<div class="mep_category_list_item">' . $this->_rootCategory->getName() . '</div>';
        Mage::helper('mep/categories')->loadCategoryHtmlTree($categoriesTree, 1, $html);
        return $html;
    }
}
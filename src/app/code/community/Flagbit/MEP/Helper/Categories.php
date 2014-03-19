<?php

class   Flagbit_MEP_Helper_Categories extends Mage_Core_Helper_Abstract
{
    protected $_selects;

    public function __construct()
    {
        $taxonomies = Mage::getModel('mep/googleTaxonomies')->getTaxonomiesForParent(0);
        $html = '';
        foreach ($taxonomies as $taxonomy)
        {
            $html .= '<option value="' . $taxonomy->getTaxonomyId() . '">' . $taxonomy->getName() . '</option>';
        }
        $this->_selects = array($html);
    }

    public function loadCategoryHtmlTree(Varien_Data_Tree_Node_Collection $categoryCollection, $recursive, &$html)
    {
        $margin = $recursive * 15;
        foreach ($categoryCollection as $category)
        {
            /** @var Varien_Data_Tree_Node $category */
            $html .= '<div class="mep_category_list_item" style="margin-left: ' . $margin . 'px">' . $category->getName() . ' <select>' . $this->_selects[0] . '</select></div>';
            if ($category->hasChildren())
            {
                $this->loadCategoryHtmlTree($category->getChildren(), $recursive + 1, $html);
            }
        }
    }
}
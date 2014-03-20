<?php

class   Flagbit_MEP_Helper_Categories extends Mage_Core_Helper_Abstract
{
    protected $_selects;

    public function __construct()
    {
        $this->_selects = array();
    }

    public function loadCategoryHtmlTree(Varien_Data_Tree_Node_Collection $categoryCollection, $recursive, &$html)
    {
        $margin = $recursive * 15;
        foreach ($categoryCollection as $category)
        {
            /** @var Varien_Data_Tree_Node $category */
            $categoryId = 'category-' . $category->getId();
            $html .= '<div id="' . $categoryId . '" class="mep_category_list_item" style="margin-left: ' . $margin . 'px">' . $category->getName() . ' ' . $this->getSelectForTaxonomy(0, $categoryId) . '</div>';
            if ($category->hasChildren())
            {
                $this->loadCategoryHtmlTree($category->getChildren(), $recursive + 1, $html);
            }
        }
    }

    public function getSelectForTaxonomy($taxonomyId, $categoryId = null)
    {
        $html = '<select class="taxonomy-select ' . $categoryId . '"><option value=""></option>' . $this->getOptionsForTaxonomy($taxonomyId) . '</select>';
        return $html;
    }

    public function getOptionsForTaxonomy($taxonomyId)
    {
        $taxonomies = $this->_getTaxonomiesForTaxonomy($taxonomyId);
        return $taxonomies['html'];
    }

    public function getArrayForTaxonomy($taxonomyId)
    {
        $taxonomies = $this->_getTaxonomiesForTaxonomy($taxonomyId);
        return $taxonomies['array'];
    }

    protected function _getTaxonomiesForTaxonomy($taxonomyId)
    {
        if (empty($this->_selects[$taxonomyId]))
        {
            $taxonomies = Mage::getModel('mep/googleTaxonomies')->getTaxonomiesForParent($taxonomyId);
            $html = '';
            $array = array();
            foreach ($taxonomies as $taxonomy)
            {
                $html .= '<option value="' . $taxonomy->getTaxonomyId() . '">' . $taxonomy->getName() . '</option>';
                $array[] = array(
                    'value' => $taxonomy->getTaxonomyId(),
                    'label' => $taxonomy->getName()
                );
            }
            $this->_selects[$taxonomyId]['html'] = $html;
            $this->_selects[$taxonomyId]['array'] = $array;
        }
        return $this->_selects[$taxonomyId];
    }
}
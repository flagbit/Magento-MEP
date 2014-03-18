<?php

class   Flagbit_MEP_Helper_Categories extends Mage_Core_Helper_Abstract
{
    public function loadCategoryHtmlTree(Varien_Data_Tree_Node_Collection $categoryCollection, $recursive, &$html)
    {
        $margin = $recursive * 15;
        foreach ($categoryCollection as $category)
        {
            /** @var Varien_Data_Tree_Node $category */
            $html .= '<div class="mep_category_list_item" style="margin-left: ' . $margin . 'px">' . $category->getName() . '</div>';
            if ($category->hasChildren())
            {
                $this->loadCategoryHtmlTree($category->getChildren(), $recursive + 1, $html);
            }
        }
    }
}
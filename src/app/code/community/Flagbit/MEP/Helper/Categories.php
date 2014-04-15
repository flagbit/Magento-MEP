<?php

class   Flagbit_MEP_Helper_Categories extends Mage_Core_Helper_Abstract
{
    protected $_selects;

    public function __construct()
    {
        $this->_selects = array();
    }

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

    public function loadCategoryHtmlTree(Varien_Data_Tree_Node_Collection $categoryCollection, $recursive, &$html)
    {
        $margin = $recursive * 15;
        foreach ($categoryCollection as $category)
        {
            /** @var Varien_Data_Tree_Node $category */
            $categoryId = $category->getId();

            $html .= '<div id="category-' . $categoryId . '" class="mep_category_list_item" style="margin-left: ' . $margin . 'px">' . $category->getName() . ' ' . $this->getMappingForTaxonomy(0, $categoryId) . '</div>';
            if ($category->hasChildren())
            {
                $this->loadCategoryHtmlTree($category->getChildren(), $recursive + 1, $html);
            }
        }
    }

    public function loadCategoryTree(Varien_Data_Tree_Node_Collection $categoryCollection, $recursive, &$array)
    {
        $margin = $recursive * 15;
        foreach ($categoryCollection as $category)
        {
            /** @var Varien_Data_Tree_Node $category */
            $categoryId = $category->getId();

            $node = array(
                'id' => $categoryId,
                'margin' => $margin,
                'name' => $category->getName(),
                'mapping' => $this->getMappingArrayForTaxonomy(0, $categoryId)
            );
            $array[] = $node;
            if ($category->hasChildren())
            {
                $this->loadCategoryTree($category->getChildren(), $recursive + 1, $array);
            }
        }
    }

    public function getMappingForTaxonomy($taxonomyId, $categoryId)
    {
        $level = 1;
        $mapping = Mage::getModel('mep/googleMapping');
        $mapping->load($categoryId, 'category_id');
        $googleMappingIds = $mapping->getGoogleMappingIds();
        if (empty($googleMappingIds))
        {
            $html = $this->getSelectForTaxonomy(0, $level, $categoryId);
            return $html;
        }
        $html = $this->getSelectForTaxonomy(0, $level, $categoryId);
        $taxonomies = explode('|', $googleMappingIds);
        foreach ($taxonomies as $taxonomy)
        {
            $jsSelect = $this->getJavascriptToSelect($taxonomy, $level, $categoryId);
            $level++;
            $html .= $this->getSelectForTaxonomy($taxonomy, $level, $categoryId);
            $html .= $jsSelect;
        }
        return $html;
    }

    public function getMappingArrayForTaxonomy($taxonomyId, $categoryId)
    {
        $level = 1;
        $mapping = Mage::getModel('mep/googleMapping');
        $mapping->load($categoryId, 'category_id');
        $googleMappingIds = $mapping->getGoogleMappingIds();
        $array = array();
        $node = array(
            'level' => $level,
            'options' => $this->getArrayForTaxonomy(0),
            'taxonomyId' => 0
        );
        if (empty($googleMappingIds))
        {
            $array[] = $node;
            return $array;
        }
        $taxonomies = explode('|', $googleMappingIds);
        $node['value'] = $taxonomies[0];
        $array[] = $node;
        foreach ($taxonomies as $taxonomy)
        {
            $level++;
            $node = array(
                'level' => $level,
                'options' => $this->getArrayForTaxonomy($taxonomy),
                'taxonomyId' => $taxonomy
            );
            if (isset($taxonomies[$level - 1]))
            {
                $node['value'] = $taxonomies[$level - 1];
            }
            $array[] = $node;
        }
        return $array;
    }

    public function getSelectForTaxonomy($taxonomyId, $level, $categoryId)
    {
        $options = $this->getOptionsForTaxonomy($taxonomyId);
        $html = '';
        if (!empty($options))
        {
            $html = '<select name="google-mapping[' . $categoryId . '][' . $level . ']" class="taxonomy-select level-' . $level . ' category-' . $categoryId . '"><option value=""></option>' . $options . '</select>';
        }
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

    public function    prepareMappingForSave($data)
    {
        $mappings = array();
        if (!empty($data))
        {
            foreach ($data as $key => $values)
            {
                $values = array_filter($values);
                if (!empty($values))
                {
                    $mappings[] = array(
                        'category_id' => $key,
                        'google_mapping_ids' => implode('|', $values),
                    );
                }
            }
        }
        return $mappings;
    }

    public function getJavascriptToSelect($taxonomyId, $level, $categoryId)
    {
        $js = '<script type="text/javascript">$$(\'.category-' . $categoryId . '.level-' . $level . '\').first().value = ' . $taxonomyId . ';</script>';
        return $js;
    }

    public function googleCategoriesAreInitialized()
    {
        return true;
    }
}
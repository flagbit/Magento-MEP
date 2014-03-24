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
            $categoryId = $category->getId();

            $html .= '<div id="category-' . $categoryId . '" class="mep_category_list_item" style="margin-left: ' . $margin . 'px">' . $category->getName() . ' ' . $this->getMappingForTaxonomy(0, $categoryId) . '</div>';
            if ($category->hasChildren())
            {
                $this->loadCategoryHtmlTree($category->getChildren(), $recursive + 1, $html);
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
}
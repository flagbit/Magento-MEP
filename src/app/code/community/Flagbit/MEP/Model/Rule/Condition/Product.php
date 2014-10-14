<?php

class Flagbit_MEP_Model_Rule_Condition_Product
    extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    /**
     * All attribute values as array in form:
     * array(
     *   [entity_id_1] => array(
     *          [store_id_1] => store_value_1,
     *          [store_id_2] => store_value_2,
     *          ...
     *          [store_id_n] => store_value_n
     *   ),
     *   ...
     * )
     *
     * Will be set only for not global scope attribute
     *
     * @var array
     */
    protected $_entityAttributeValues = null;

    public function __construct() {
        parent::__construct();
        $this->setJsFormObject('mep_conditions_fieldset');
    }

    public function getValueParsed()
    {
        if (!$this->hasValueParsed()) {
            $value = $this->getData('value');
            if ($this->isArrayOperatorType() && is_string($value)) {
                $value = preg_split('#\s*[,;]\s*#', $value, null, PREG_SPLIT_NO_EMPTY);
            }
            if ($this->getAttribute() == 'category_ids') {
                $categories = array();
                if (!is_array($value)) {
                    $value = array($value);
                }
                $this->getAllChildrenCategories($value, $categories);
                $value = $categories;
            }
            $this->setValueParsed($value);
        }
        return $this->getData('value_parsed');
    }

    protected function  getAllChildrenCategories($categoriesIds, &$categories) {
        if (!is_array($categoriesIds)) {
            $categoriesIds = explode(',', $categoriesIds);
        }
        foreach ($categoriesIds as $categoryId) {
            if (!in_array($categoryId, $categories)) {
                $categories[] = $categoryId;
            }
            $cat = Mage::getModel('catalog/category')->load($categoryId);
            $childrenCats = $cat->getChildren();
            if (!empty($childrenCats)) {
                $childrenCats = explode(',', $childrenCats);
                $this->getAllChildrenCategories($childrenCats, $categories);
            }
        }
    }

    /**
     * Retrieve attribute object
     *
     * @return Flagbit_MEP_Model_Rule_Condition_Product Self.
     */
    public function getAttributeObject()
    {
        try {
            $obj = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $this->getAttribute());
        } catch (Exception $e) {
            $obj = new Varien_Object();
            $obj->setEntity(Mage::getResourceSingleton('catalog/product'))->setFrontendInput('text');
        }
        return $obj;
    }

    /**
     * Add special attributes
     *
     * @param array &$attributes Attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes['attribute_set_id'] = Mage::helper('mep')->__('Attribute Set');
        $attributes['category_ids'] = Mage::helper('mep')->__('Category');
    }

    /**
     * Load attribute options
     *
     * @return Flagbit_MEP_Model_Rule_Condition_Product Self.
     */
    public function loadAttributeOptions()
    {
        $productAttributes = Mage::getResourceSingleton('catalog/product')->loadAllAttributes()->getAttributesByCode();
        $attributes = array();
        foreach ($productAttributes as $attribute) {
            if ($attribute->getData('is_visible')) {
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }
        $this->_addSpecialAttributes($attributes);
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Retrieve value by option
     *
     * @param mixed $option
     * @return string Value of an Option
     */
    public function getValueOption($option = null)
    {
        if (!$this->getData('value_option')) {
            if ($this->getAttribute() === 'attribute_set_id') {
                $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();

                $options = Mage::getResourceModel('eav/entity_attribute_set_collection')
                    ->setEntityTypeFilter($entityTypeId)
                    ->load()
                    ->toOptionHash();
                $this->setData('value_option', $options);
            } elseif (is_object($this->getAttributeObject()) && $this->getAttributeObject()->usesSource()) {
                if ($this->getAttributeObject()->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }

                $optionsArr = $this->getAttributeObject()->getSource()->getAllOptions($addEmptyOption);
                $options = array();
                foreach ($optionsArr as $o) {
                    if (is_array($o['value'])) {
                        // Do nothing
                    } else {
                        $options[$o['value']] = $o['label'];
                    }
                }
                $this->setData('value_option', $options);
            }
        }
        return $this->getData('value_option' . (!is_null($option) ? '/' . $option : ''));
    }

    /**
     * Retrieve select option values
     *
     * @return array Select Options
     */
    public function getValueSelectOptions()
    {
        if (!$this->getData('value_select_options')) {
            if ($this->getAttribute() === 'attribute_set_id') {
                $entityTypeId = Mage::getSingleton('eav/config')
                    ->getEntityType('catalog_product')->getId();
                $options = Mage::getResourceModel('eav/entity_attribute_set_collection')
                    ->setEntityTypeFilter($entityTypeId)
                    ->load()->toOptionArray();
                $this->setData('value_select_options', $options);
            } elseif (is_object($this->getAttributeObject()) && $this->getAttributeObject()->usesSource()) {
                if ($this->getAttributeObject()->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $optionsArr = $this->getAttributeObject()->getSource()->getAllOptions($addEmptyOption);
                $this->setData('value_select_options', $optionsArr);
            }
        }
        return $this->getData('value_select_options');
    }

    /**
     * Retrieve after element HTML
     *
     * @return string Element HTML
     */
    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $image = Mage::getDesign()->getSkinUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger">
                <img src="' . $image . '" alt="" class="v-middle rule-chooser-trigger"
                title="' . Mage::helper('rule')->__('Open Chooser') . '" /></a>';
        }
        return $html;
    }

    /**
     * Retrieve attribute element
     *
     * @return Varien_Form_Element_Abstract Element
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Retrieve input type
     *
     * @return string Input Type
     */
    public function getInputType()
    {
        if ($this->getAttribute() === 'attribute_set_id') {
            return 'select';
        }

        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }

        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                $frontendInput = 'select';
                break;
            case 'multiselect':
                $frontendInput = 'multiselect';
                break;
            case 'date':
                $frontendInput = 'date';
                break;
            default:
                $frontendInput = 'string';
                break;
        }

        return $frontendInput;
    }

    /**
     * Retrieve value element type
     *
     * @return string Element Type
     */
    public function getValueElementType()
    {
        if ($this->getAttribute() === 'attribute_set_id') {
            return 'select';
        }

        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }

        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                $frontendInput = 'select';
                break;
            case 'multiselect':
                $frontendInput = 'multiselect';
                break;
            case 'date':
                $frontendInput = 'date';
                break;
            default:
                $frontendInput = 'text';
                break;
        }

        return $frontendInput;
    }

    /**
     * Retrieve value element
     *
     * @return Varien_Data_Form_Element_Abstract Element
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    $element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
                    break;
            }
        }
        return $element;
    }

    /**
     * Retrieve Explicit Apply
     *
     * @return boolean True/False
     */
    public function getExplicitApply()
    {
        $return = false;

        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $return = true;
                break;
        }

        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    $return = true;
                    break;
            }
        }

        return $return;
    }

    /**
     * Load array
     *
     * @param array $arr Attribute Array
     * @return Flagbit_MEP_Model_Rule_Condition_Product Self.
     */
    public function loadArray($arr)
    {
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $attribute = $this->getAttributeObject();

        if ($attribute && $attribute->getBackendType() == 'decimal') {
            if (isset($arr['value'])) {
                $arr['value'] = Mage::app()->getLocale()->getNumber($arr['value']);
            } else {
                $arr['value'] = false;
            }

            if (isset($arr['is_value_parsed'])) {
                $arr['is_value_parsed'] = Mage::app()->getLocale()->getNumber($arr['is_value_parsed']);
            } else {
                $arr['is_value_parsed'] = false;
            }
        }

        return parent::loadArray($arr);
    }

    /**
     * Check if value should be array
     *
     * Depends on operator input type
     *
     * @return bool
     */
    public function isArrayOperatorType()
    {
        if ($this->getAttribute() == 'category_ids') {
            return true;
        }
        $op = $this->getOperator();
        return $op === '()' || $op === '!()' || in_array($this->getInputType(), $this->_arrayInputTypes);
    }

    /**
     * Validate product attrbute value for condition
     *
     * @param   mixed $validatedValue product attribute value
     * @return  bool
     */
    public function validateAttribute($validatedValue)
    {
        if (is_object($validatedValue)) {
            return false;
        }

        /**
         * Condition attribute value
         */
        $value = $this->getValueParsed();

        /**
         * Comparison operator
         */
        $op = $this->getOperatorForValidate();

        // if operator requires array and it is not, or on opposite, return false
        if ($this->isArrayOperatorType() xor is_array($value)) {
            return false;
        }

        $result = false;

        switch ($op) {
            case '==': case '!=':
            if (is_array($value)) {
                if (is_array($validatedValue)) {
                    $result = array_intersect($value, $validatedValue);
                    $result = !empty($result);
                } else {
                    return false;
                }
            } else {
                if (is_array($validatedValue) && $validatedValue == 1) {
                    $result = array_shift($validatedValue) == $value;
                } elseif (is_array($validatedValue) && $validatedValue > 1) {
                    $result = in_array($value, $validatedValue);
                } else {
                    $result = $this->_compareValues($validatedValue, $value);
                }
            }
            break;

            case '<=': case '>':
            if (!is_scalar($validatedValue)) {
                return false;
            } else {
                $result = $validatedValue <= $value;
            }
            break;

            case '>=': case '<':
            if (!is_scalar($validatedValue)) {
                return false;
            } else {
                $result = $validatedValue >= $value;
            }
            break;

            case '{}': case '!{}':
            if (is_scalar($validatedValue) && is_array($value)) {
                foreach ($value as $item) {
                    if (stripos($validatedValue,$item)!==false) {
                        $result = true;
                        break;
                    }
                }
            } elseif (is_array($value)) {
                if (is_array($validatedValue)) {
                    $result = array_intersect($value, $validatedValue);
                    $result = !empty($result);
                } else {
                    return false;
                }
            } else {
                if (is_array($validatedValue)) {
                    $result = in_array($value, $validatedValue);
                } else {
                    $result = $this->_compareValues($value, $validatedValue, false);
                }
            }
            break;

            case '()': case '!()':
            if (is_array($validatedValue)) {
                $result = count(array_intersect($validatedValue, (array)$value))>0;
            } else {
                $value = (array)$value;
                foreach ($value as $item) {
                    if ($this->_compareValues($validatedValue, $item)) {
                        $result = true;
                        break;
                    }
                }
            }
            break;
        }

        if ('!=' == $op || '>' == $op || '<' == $op || '!{}' == $op || '!()' == $op) {
            $result = !$result;
        }

        return $result;
    }

    public function validate(Varien_Object $object)
    {
        $attrCode = $this->getAttribute();
        if ('category_ids' == $attrCode) {
            return $this->validateAttribute($object->getAvailableInCategories());
        }
        if ('attribute_set_id' == $attrCode) {
            return $this->validateAttribute($object->getData($attrCode));
        }

        $oldAttrValue = $object->hasData($attrCode) ? $object->getData($attrCode) : null;
        $newValue = $oldAttrValue ? $oldAttrValue : $this->_getAttributeValue($object);
        $object->setData($attrCode, $newValue);
        $result = $this->_validateProduct($object);
        $this->_restoreOldAttrValue($object, $oldAttrValue);

        return (bool)$result;
    }

    /**
     * Get attribute value.
     *
     * Backward compatibility with versions < 1.13.0.0
     *
     * @param Varien_Object $object
     * @return mixed
     */
    protected function _getAttributeValue(Varien_Object $object) {
        // just use the parent in case the method change in future versions
        if (version_compare(Mage::getVersion(), '1.13.0.0') >= 0) {
            return parent::_getAttributeValue($object);
        }

        // reproduce functionality from 1.13
        $attrCode = $this->getAttribute();
        $storeId = $object->getStoreId();
        $defaultStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $productValues  = isset($this->_entityAttributeValues[$object->getId()])
            ? $this->_entityAttributeValues[$object->getId()] : array();
        $defaultValue = isset($productValues[$defaultStoreId])
            ? $productValues[$defaultStoreId] : $object->getData($attrCode);
        $value = isset($productValues[$storeId]) ? $productValues[$storeId] : $defaultValue;

        $value = $this->_prepareDatetimeValue($value, $object);
        $value = $this->_prepareMultiselectValue($value, $object);

        return $value;

    }

    /**
     * Prepare datetime attribute value
     *
     * Backward compatibility with versions < 1.13.0.0
     *
     * @param mixed $value
     * @param Varien_Object $object
     * @return mixed
     */
    protected function _prepareDatetimeValue($value, $object)
    {
        // just use the parent in case the method change in future versions
        if (version_compare(Mage::getVersion(), '1.13.0.0') >= 0) {
            return parent::_prepareDatetimeValue($value, $object);
        }

        // reproduce functionality from 1.13
        $attribute = $object->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getBackendType() == 'datetime') {
            $value = strtotime($value);
        }
        return $value;
    }

    /**
     * Prepare multiselect attribute value
     *
     * Backward compatibility with versions < 1.13.0.0
     *
     * @param mixed $value
     * @param Varien_Object $object
     * @return mixed
     */
    protected function _prepareMultiselectValue($value, $object)
    {
        // just use the parent in case the method change in future versions
        if (version_compare(Mage::getVersion(), '1.13.0.0') >= 0) {
            return parent::_prepareMultiselectValue($value, $object);
        }

        // reproduce functionality from 1.13
        $attribute = $object->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getFrontendInput() == 'multiselect') {
            $value = strlen($value) ? explode(',', $value) : array();
        }
        return $value;
    }

    /**
     * Validate product
     *
     * Backward compatibility with versions < 1.13.0.0
     *
     * @param Varien_Object $object
     * @return bool
     */
    protected function _validateProduct($object)
    {
        return Mage_Rule_Model_Condition_Abstract::validate($object);
    }

    /**
     * Restore old attribute value
     *
     * Backward compatibility with versions < 1.13.0.0
     *
     * @param Varien_Object $object
     * @param mixed $oldAttrValue
     */
    protected function _restoreOldAttrValue($object, $oldAttrValue)
    {
        // just use the parent in case the method change in future versions
        if (version_compare(Mage::getVersion(), '1.13.0.0') >= 0) {
            parent::_restoreOldAttrValue($object, $oldAttrValue);
        }

        $attrCode = $this->getAttribute();
        if (is_null($oldAttrValue)) {
            $object->unsetData($attrCode);
        } else {
            $object->setData($attrCode, $oldAttrValue);
        }
    }
}

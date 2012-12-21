<?php

class Flagbit_MEP_Model_Attribute_Mapping extends Mage_Core_Model_Abstract
{
    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('mep/attribute_mapping');
    }

    /**
     * get Form Values
     *
     * @return array
     */
    public function getValuesForForm()
    {
        /* @var $attributes Mage_Eav_Model_Resource_Entity_Attribute_Collection */
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection');

        $attributes->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                    ->addFieldToFilter('frontend_input', array('in' => array('select', 'multiselect')));

        $options = array();
        $options[] = array(
            'label' => Mage::helper('mep')->__('Categories'),
            'value' => 'category'
        );

        foreach($attributes as $attribute){
            $options[] = array(
                'label' => sprintf('%s (%s)', $attribute->getFrontendLabel(), $attribute->getAttributeCode()),
                'value' => $attribute->getAttributeCode()
            );
        }

        usort ($options, array($this, '_sortValuesForForm'));

        return $options;
    }

    /**
     * usort callback
     *
     * @param $a
     * @param $b
     * @return int
     */
    protected function _sortValuesForForm($a, $b)
    {
        return strcmp($a["label"], $b["label"]);
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        //validate attribute_code
        $validatorAttrCode = new Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{1,254}$/'));
        if (!$validatorAttrCode->isValid($this->getAttributeCode())) {
            Mage::throwException(Mage::helper('mep')->__('Attribute code is invalid. Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'));
        }
        return parent::_beforeSave();
    }
}
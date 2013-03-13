<?php
class Flagbit_MEP_Model_Rule_Condition_Combine
    extends Mage_CatalogRule_Model_Rule_Condition_Combine
{
    /**
     * Class Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns the aggregator options
     *
     * @see Mage_Rule_Model_Condition_Combine::loadAggregatorOptions()
     * @return FireGento_DynamicCategory_Model_Rule_Condition_Combine Self.
     */
    public function loadAggregatorOptions()
    {
        $this->setAggregatorOption(
            array(
                'all' => Mage::helper('rule')->__('ALL'),
            )
        );
        return $this;
    }

    /**
     * Returns the value options
     *
     * @see Mage_Rule_Model_Condition_Combine::loadValueOptions()
     * @return FireGento_DynamicCategory_Model_Rule_Condition_Combine Self.
     */
    public function loadValueOptions()
    {
        $this->setValueOption(
            array(
                1 => Mage::helper('rule')->__('TRUE'),
                //0 => Mage::helper('rule')->__('FALSE'),
            )
        );
        return $this;
    }

    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('mep/rule_condition_product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($productAttributes as $code=>$label) {
            $attributes[] = array('value'=>'catalogrule/rule_condition_product|'.$code, 'label'=>$label);
        }
        return $attributes;
    }
}

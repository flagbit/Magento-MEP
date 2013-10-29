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


    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('mep/rule_condition_product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($productAttributes as $code=>$label) {
            $attributes[] = array('value'=>'mep/rule_condition_product|'.$code, 'label'=>$label);
        }
        return $attributes;
    }
}

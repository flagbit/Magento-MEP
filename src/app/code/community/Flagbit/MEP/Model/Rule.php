<?php
/**
 * This file is part of the FIREGENTO project.
 *
 * FireGento_DynamicCategory is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This script is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * PHP version 5
 *
 * @category  FireGento
 * @package   FireGento_DynamicCategory
 * @author    FireGento Team <team@firegento.com>
 * @copyright 2012 FireGento Team (http://www.firegento.de). All rights served.
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version   1.0.0
 * @since     0.2.0
 */
/**
 * Rules for Conditions
 *
 * @category  FireGento
 * @package   FireGento_DynamicCategory
 * @author    FireGento Team <team@firegento.com>
 * @copyright 2012 FireGento Team (http://www.firegento.de). All rights served.
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version   1.0.0
 * @since     0.2.0
 */
class Flagbit_MEP_Model_Rule extends Mage_CatalogRule_Model_Rule
{
    /**
     * Enter description here ...
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mep/rule');
    }

    /**
     * Gets an instance of the respective conditions model
     *
     * @see Mage_Rule_Model_Rule::getConditionsInstance()
     * @return FireGento_DynamicCategory_Model_Rule_Condition_Combine Condition Instance
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('mep/rule_condition_combine');
    }

    /**
     * Enter description here ...
     *
     * @param array $rule
     * @return FireGento_DynamicCategory_Model_Rule
     */
    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);
        if(isset($arr['type']) && $arr['type'] == 'catalogrule/rule_condition_product') {
            $arr['type'] = 'mep/rule_condition_product';
        }
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions(array())->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['actions'])) {
            $this->getActions()->setActions(array())->loadArray($arr['actions'][1]);
        }
        return $this;
    }

    /**
     * Callback function for product matching
     *
     * @param $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
        }
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds(Mage_Catalog_Model_Resource_Product_Collection $collection = null)
    {
        if (is_null($this->_productIds)) {
            $this->_productIds = array();
            $this->setCollectedAttributes(array());
            //$websiteIds = explode(',', $this->getWebsiteIds());

            //if ($websiteIds) {
            $this->getConditions()->collectValidatedAttributes($collection);
            Mage::getSingleton('core/resource_iterator')->walk(
                $collection->getSelect(),
                array(array($this, 'callbackValidateProduct')),
                array(
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => Mage::getModel('catalog/product'),
                )
            );
            //}
        }
        return $this->_productIds;
    }
}

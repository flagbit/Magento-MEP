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

    protected $_profile;

    public function setProfile($profile) {
        $this->_profile = $profile;
    }
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
     * @return Flagbit_MEP_Model_Rule_Condition_Combine Condition Instance
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('mep/rule_condition_combine');
    }

    /**
     * Enter description here ...
     *
     * @param array $rule
     * @return Flagbit_MEP_Model_Rule
     */
    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'][1]['conditions']) && is_array($arr['conditions'][1]['conditions'])) {
            foreach ($arr['conditions'][1]['conditions'] as &$condition) {
                if ($condition['type'] == 'catalogrule/rule_condition_product') {
                    $condition['type'] = 'mep/rule_condition_product';
                }
            }
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
        if ($this->getConditions() && $this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
        }
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        $settings = $this->_profile->getSettings();
        if (!is_array($settings)) {
            $settings = unserialize($settings);
        }
        if (!empty($settings['is_in_stock']) && $settings['is_in_stock'] == 2) {
            $settings['is_in_stock'] = '';
        }
        if (is_null($this->_productIds)) {
            $this->_productIds = array();
            $this->setCollectedAttributes(array());

            if ($this->getWebsiteIds()) {
                /** @var $productCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
                $productCollection = Mage::helper('mep')->getProductsCollection();
                if (isset($settings['apply_to']) && !is_null($settings['apply_to'])) {
                    $productCollection->addAttributeToFilter('type_id', array('in' => $settings['apply_to']));
                }
                if (isset($settings['is_in_stock']) && strlen($settings['is_in_stock']))
                {
                    $productCollection->getSelect()->where('is_in_stock = ?', intval($settings['is_in_stock']));
                }
                if (!empty($settings['qty'])) {
                    if (isset($settings['qty']['threshold']) && strlen($settings['qty']['threshold'])) {
                        $operator = Mage::helper('mep/qtyFilter')->getOperatorForSqlFilter($settings['qty']['operator']);
                        $threshold = $settings['qty']['threshold'];
                        $productCollection->getSelect()->where('qty ' . $operator . ' ?', $threshold);
                    }
                }
                if ($this->_profile->getStoreId() != 0)
                {
                    $productCollection->addWebsiteFilter($this->getWebsiteIds());
                }
                if ($this->_productsFilter)
                {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $select = $productCollection->getSelect();
                Mage::log($select->assemble(), null, 'mep-1.log');
                $this->getConditions()->collectValidatedAttributes($productCollection);
                $this->_walk(
                    $select,
                    array(array($this, 'callbackValidateProduct')),
                    array(
                        'attributes' => $this->getCollectedAttributes(),
                        'product'    => Mage::getModel('catalog/product'),
                    )
                );
            }
        }

        return $this->_productIds;
    }

    protected function _walk($query, array $callbacks, array $args=array(), $adapter = null)
    {
        $stmt = $this->_getStatement($query, $adapter);
        $args['idx'] = 0;
        while ($row = $stmt->fetch()) {
            $args['row'] = $row;
            foreach ($callbacks as $callback) {
                $result = call_user_func($callback, $args);
                if (!empty($result)) {
                    $args = array_merge($args, $result);
                }
            }
            $args['idx']++;
            if ($limit = $this->getData('limit')) {
                if (!is_null($this->_productIds) && count($this->_productIds) == $limit)
                {
                    break ;
                }
            }
        }

        return $this;
    }

    protected function _getStatement($query, $conn = null)
    {
        if ($query instanceof Zend_Db_Statement_Interface) {
            return $query;
        }

        if ($query instanceof Zend_Db_Select) {
            return $query->query();
        }

        if (is_string($query)) {
            if (!$conn instanceof Zend_Db_Adapter_Abstract) {
                Mage::throwException(Mage::helper('core')->__('Invalid connection'));
            }
            return $conn->query($query);
        }

        Mage::throwException(Mage::helper('core')->__('Invalid query'));
    }

}

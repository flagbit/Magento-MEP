<?php
/**
 * Helper
 *
 * @category Flagbit_MEP
 * @package Flagbit_MEP
 * @author Damian Luszczymak <damian.luszczymak@flagbit.de>
 * @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
 * @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version 0.1.0
 * @since 0.1.0
 */

class Flagbit_MEP_Model_Data extends Mage_Catalog_Model_Convert_Parser_Product
{
    protected $_externalFields = array();

    /**
     * @desc Retrieve accessible external product attributes
     * @return array
     * @see Mage_Catalog_Model_Convert_Parser_Product::getExternalAttributes()
     */
    public function getExternalAttributes($profileId = null)
    {
        $attributes = $this->_externalFields;

        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load();

        foreach ($collection as $attributeSet) {
            $attributes += $this->getAttributesBySet($attributeSet->getAttributeSetId());
        }

        foreach ($this->_inventoryFields as $field) {
            $attributes[$field] = $field;
        }

        // added for url mapping
        $attributes['price_catalog_rule'] = 'price with catalog rule';
        $attributes['url'] = 'url';
        $attributes['_category'] = 'category';
        $attributes['_category_id'] = 'category_id';
        $attributes['google_mapping'] = 'google_mapping';
        $attributes['image_url'] = 'image_url';
        $attributes['gross_price'] = 'gross_price';
        $attributes['special_price'] = 'special_price';
        $attributes['special_from_date'] = 'special_from_date';
        $attributes['special_to_date'] = 'special_to_date';
        $attributes['fixed_value_format'] = 'fixed_value_format';
        $attributes['is_salable'] = 'is_salable';
        $attributes['entity_id'] = 'entity_id';
        $attributes['created_at'] = 'created_at';
        $attributes['updated_at'] = 'updated_at';
        $attributes['_type'] = 'type';

        //Adding special attribute from DerModProd
        if(Mage::helper('core')->isModuleEnabled('DerModPro_BasePrice')) {
            $attributes['base_price_amount'] = 'base_price_amount';
            $attributes['base_price_unit'] = 'base_price_unit';
            $attributes['base_price_base_amount'] = 'base_price_base_amount';
            $attributes['base_price_base_unit'] = 'base_price_base_unit';
            $attributes['base_price_reference_amount'] = 'base_price_reference_amount';
        }


        // add attribute mapping attributes
        $attributeMappingCollection = Mage::getResourceModel('mep/attribute_mapping_collection')->load();
        foreach($attributeMappingCollection as $attributeMapping){
            $attributes[$attributeMapping->getAttributeCode()] = sprintf('%s (%s)', $attributeMapping->getName(), $attributeMapping->getAttributeCode());
        }

        //add shipping attributes
        $shipping_id = Mage::getModel('mep/profile')->load($profileId)->getShippingId();
        if (!empty($shipping_id)) {
            $collection = Mage::getModel('mep/shipping_attribute')->getCollection()
                ->addFieldToFilter('profile_id', array('eq' => $shipping_id));
            foreach ($collection as $item) {
                $attributes[$item->getAttributeCode()] = sprintf('%s (%s + %s)',$item->getAttributeCode(), $item->getShippingMethod(), $item->getPaymentMethod());
            }
        }

        return $attributes;
    }

    /**
     * @desc Retrieve Attribute Set Group Tree as JSON format
     * @param $setId
     * @return string
     */
    public function getAttributesBySet($setId)
    {
        $items = array();

        /* @var $groups Mage_Eav_Model_Mysql4_Entity_Attribute_Group_Collection */
        $groups = Mage::getModel('eav/entity_attribute_group')
            ->getResourceCollection()
            ->setAttributeSetFilter($setId)
            ->load();

        /* @var $node Mage_Eav_Model_Entity_Attribute_Group */
        foreach ($groups as $node) {
            /** @var $nodeChildren Mage_Catalog_Model_Resource_Category_Attribute_Collection */
            $nodeChildren = Mage::getResourceModel('catalog/product_attribute_collection')
                ->setAttributeGroupFilter($node->getId())
                ->addVisibleFilter()
                ->checkConfigurableProducts()
                ->addStoreLabel(Mage::app()->getStore()->getId());

            foreach ($nodeChildren as $child) {

                if (in_array($child->getAttributeCode(), $this->_internalFields) || $child->getFrontendInput() == 'hidden') {
                    continue;
                }

                $items[$child->getAttributeCode()] = $child->getAttributeCode() . " (" . $child->getStoreLabel() . ")";
            }
        }

        return $items;
    }


    public function getAllActivePaymentMethods()
    {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
            $methods[$paymentCode] = $paymentTitle;
        }
        return $methods;
    }

    public function getAllShippingMethods()
    {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

        $options = array();

        /** @var $_carrier Mage_Shipping_Model_Carrier_Abstract */
        foreach ($methods as $_ccode => $_carrier) {
            $_methodOptions = array();
            if ($_methods = $_carrier->getAllowedMethods()) {
                foreach ($_methods as $_mcode => $_method) {
                    $_code = $_ccode . '_' . $_mcode;
                    $_methodOptions[] = array('value' => $_code, 'label' => $_method);
                }

                if (!$_title = Mage::getStoreConfig('carriers/' . $_ccode . '/title'))
                    $_title = $_ccode;

                $options[$_code] = $_title;
            }
        }

        return $options;
    }

}

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
        $_helper = Mage::helper('mep');

        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load();

        foreach ($collection as $attributeSet) {
            $attributes[preg_replace('/([^A-Za-z_-]*)/', '', $attributeSet->getAttributeSetName())] = $this->getAttributesBySet($attributeSet->getAttributeSetId());
        }

        foreach ($this->_inventoryFields as $field) {
            $attributes[$field] = $field;
        }

        // added for url mapping
        $specialAttributes = array();
        $specialAttributes['url'] = 'url';
        $specialAttributes['_category'] = 'category';
        $specialAttributes['google_mapping'] = 'google_mapping';
        $specialAttributes['_category_id'] = 'category_id';
        $specialAttributes['image_url'] = 'image_url';
        $specialAttributes['gross_price'] = 'gross_price';
        $specialAttributes['special_price'] = 'special_price';
        $specialAttributes['special_from_date'] = 'special_from_date';
        $specialAttributes['special_to_date'] = 'special_to_date';
        $specialAttributes['fixed_value_format'] = 'fixed_value_format';
        $specialAttributes['is_salable'] = 'is_salable';
        $specialAttributes['entity_id'] = 'entity_id';
        $attributes[$_helper->__('Special Attributes')] = $specialAttributes;

        //Adding special attribute from DerModProd
        if(Mage::helper('core')->isModuleEnabled('DerModPro_BasePrice')) {
            $specialAttributes = array();
            $specialAttributes['base_price_amount'] = 'base_price_amount';
            $specialAttributes['base_price_unit'] = 'base_price_unit';
            $specialAttributes['base_price_base_amount'] = 'base_price_base_amount';
            $specialAttributes['base_price_base_unit'] = 'base_price_base_unit';
            $specialAttributes['base_price_reference_amount'] = 'base_price_reference_amount';
            $attributes[$_helper->__('DerModPro')] = $specialAttributes;
        }


        // add attribute mapping attributes
        $attributeMappingCollection = Mage::getResourceModel('mep/attribute_mapping_collection')->load();
        foreach($attributeMappingCollection as $attributeMapping){
            $attributes[$_helper->__('Mappings')][$attributeMapping->getAttributeCode()] = sprintf('%s (%s)', $attributeMapping->getName(), $attributeMapping->getAttributeCode());
        }

        //add shipping attributes
        $shipping_id = Mage::getModel('mep/profile')->load($profileId)->getShippingId();
        if (!empty($shipping_id)) {
            $collection = Mage::getModel('mep/shipping_attribute')->getCollection()
                ->addFieldToFilter('profile_id', array('eq' => $shipping_id));
            foreach ($collection as $item) {
                $attributes[$_helper->__('Shipping')][$item->getAttributeCode()] = sprintf('%s (%s + %s)',$item->getAttributeCode(), $item->getShippingMethod(), $item->getPaymentMethod());
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

            $nodeChildren->getSelect()->where('main_table.is_user_defined = ?', 1);

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

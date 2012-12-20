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
    public function getExternalAttributes($shipping_id = 0)
    {
        $attributes = $this->_externalFields;

        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load();

        foreach ($collection as $attributeSet) {

            $attributes[preg_replace('/([^A-Za-z_-]*)/', '', $attributeSet->getAttributeSetName())] = $this->getAttributesBySet($attributeSet->getAttributeSetId());
        }

        foreach ($this->_inventoryFields as $field) {
            $attributes[$field] = $field;
        }

        //add shipping attributes
        if(!empty($shipping_id)) {
            $collection = Mage::getModel('mep/shipping_attribute')->getCollection();
            $collection->addFieldToFilter('profile_id', array('eq' => $shipping_id));
            foreach($collection as $item) {
                $attributes[$item->getAttributeCode()] = $item->getShippingMethod().'+'.$item->getPaymentMethod();
            }
        }


        // added for url mapping
        $attributes['url']                      = 'url';
        $attributes['_category']                = 'category';
        $attributes['image_url']                = 'image_url';
        $attributes['gross_price']              = 'gross_price';
        $attributes['fixed_value_format']       = 'fixed_value_format';
        $attributes['entity_id']                = 'entity_id';


        //TODO HACK THE PLANET
        $attributes['versandkosten_paypal']     = 'Versandkosten PayPal Standard';
        $attributes['versandkosten_vorkasse']   = 'Versandkosten Vorkasse';
        $attributes['versandkosten_nachnahme']  = 'Versandkosten Nachnahme';
        $attributes['versandkosten_sofort']     = 'Versandkosten Sofortüberweisung';
        $attributes['versandkosten_creditcard'] = 'Versandkosten Kreditkarte';

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

                $items[$child->getAttributeCode()] = $child->getAttributeCode()." (".$child->getStoreLabel().")";
            }
        }

        return $items;
    }


    public function getAllActivPaymentMethods()
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

        foreach ($methods as $_ccode => $_carrier) {
            $_methodOptions = array();
            if ($_methods = $_carrier->getAllowedMethods()) {
                foreach ($_methods as $_mcode => $_method) {
                    $_code = $_ccode . '_' . $_mcode;
                    $_methodOptions[] = array('value' => $_code, 'label' => $_method);
                }

                if (!$_title = Mage::getStoreConfig('carriers/'.$_ccode.'/title'))
                    $_title = $_ccode;

                $options[$_mcode] = $_title;
            }
        }

        return $options;
    }

}

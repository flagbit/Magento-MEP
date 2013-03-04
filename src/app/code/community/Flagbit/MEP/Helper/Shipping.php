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

class Flagbit_MEP_Helper_Shipping extends Mage_Core_Helper_Abstract
{

    private $_groupId = '0';
    private $_storeId = '0';
    private $_websiteId = '0';
    private $orderData = array();

    protected $_quote = null;
    protected $_shippingRequest = null;
    protected $_store = array();

    /**
     * get Quote Object
     *
     * @param bool $reset
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($reset = false)
    {
        if($this->_quote == null){
            $this->_quote = Mage::getModel('sales/quote');
        }
        if($reset === true){
            $this->_quote->getCollection()->clear();
        }
        return $this->_quote;
    }

    /**
     * get Quote Item Object
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    protected function _getQuoteItem()
    {
        return Mage::getModel('sales/quote_item');
    }

    /**
     * get Shipping Request Object
     *
     * @return Mage_Shipping_Model_Rate_Request
     */
    protected function _getShippingRequest()
    {
        if($this->_shippingRequest == null){
            $this->_shippingRequest = Mage::getModel('shipping/rate_request');
        }
        return $this->_shippingRequest;
    }

    /**
     * get Store Object
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore($storeId)
    {
        if(!isset($this->_store[$storeId])){
            $this->_store[$storeId] = Mage::getModel('core/store')->load($storeId);
        }
        return $this->_store[$storeId];
    }

    /**
     * calculate Shippingcosts
     *
     * @param $product Mage_Catalog_Model_Product
     * @param $store_id int
     * @param $profile Flagbit_MEP_Model_Profile
     */
    public function emulateCheckout($product, $store_id, $profile)
    {
        $store = $this->_getStore($store_id);
        $productPrice = $product->getFinalPrice();
        $productWeight = $product->getWeight();

        /** @var $request Mage_Shipping_Model_Rate_Request */
        $request = $this->_getShippingRequest();

        $quoteItem = $this->_getQuoteItem()->setProduct($product)->setQty(1);

        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $this->_getQuote(true)->setStore($store);

        $quote->addItem($quoteItem);
        $quoteItem->calcRowTotal();
        $quoteItem->setRowTotal($productPrice);

        $request->setAllItems(array( $quoteItem ));
        $request->setOrig(true);
        $request->setDestCountryId($profile->getCountry());

        $request->setCountryId($profile->getCountry());
        $request->setRegionId(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_REGION_ID, $store_id));
        $request->setCity(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY, $store_id));
        $request->setPostcode(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP, $store_id));
        $request->setPackageValue($productPrice);
        $request->setPackageValueWithDiscount($productPrice);
        $request->setPackageWeight($productWeight);
        $request->setPackageQty(1);
        $request->setPackagePhysicalValue($productPrice);

        $request->setFreeMethodWeight(0);

        $request->setStoreId($store_id);
        $request->setWebsiteId($store->getWebsiteId());
        $request->setFreeShipping(null);

        /**
         * Currencies need to convert in free shipping
         */
        $request->setBaseCurrency('EUR');
        $request->setPackageCurrency('EUR');
        //$request->setLimitCarrier($this->getLimitCarrier());

        $request->setBaseSubtotalInclTax($productPrice);

        $result = Mage::getModel('shipping/shipping')->collectRates($request)->getResult();

        if ($result) {
            $shippingRates = $result->getAllRates();

            foreach ($shippingRates as $shippingRate) {
                $rate = Mage::getModel('sales/quote_address_rate')
                    ->importShippingRate($shippingRate);

                if($rate->getCode() == $profile->getShippingMethod()){
                    break;
                }
            }
            if($rate instanceof Mage_Sales_Model_Quote_Address_Rate){
                Mage::dispatchEvent('mep_calculate_shipping_rate', array(
                    'product' => $product, // Mage_Catalog_Model_Product
                    'profile' => $profile, // Flagbit_MEP_Model_Profile
                    'rate' => $rate // Mage_Sales_Model_Quote_Address_Rate
                    )
                );
                return $rate->getPrice();
            }
        }
        return 0;
    }
}
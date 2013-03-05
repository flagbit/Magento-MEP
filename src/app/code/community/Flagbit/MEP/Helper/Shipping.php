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

    protected $_groupId = '0';
    protected $_storeId = '0';
    protected $_websiteId = '0';
    protected $orderData = array();

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
        switch($profile->getCheckoutType()){

            case 'quote':
                return $this->emulateWithQuoteCheckout($product, $store_id, $profile);
                break;

            default:
                return $this->emulateByShippingRequestCheckout($product, $store_id, $profile);
                break;
        }
    }

    /**
     * calculate Shippingcosts
     *
     * @param $product Mage_Catalog_Model_Product
     * @param $store_id int
     * @param $profile Flagbit_MEP_Model_Profile
     */
    public function emulateByShippingRequestCheckout($product, $store_id, $profile)
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

    public function emulateWithQuoteCheckout($item, $store_id, $profile)
     {
         $this->_product = $item;
         $this->_storeId = $store_id;
         $this->_websiteId = Mage::getModel('core/store')->load($store_id)->getWebsiteId();

         $this->setOrderInfo($profile->getPaymentMethod(), $profile->getCountry(), $profile->getShippingMethod());
         $orderData = $this->orderData;
         if (!empty($orderData)) {
             $this->_initSession($orderData['session']);
             $quote = $this->_getOrderCreateModel()->getQuote();
             $address = $quote->getShippingAddress();

             $this->_getOrderCreateModel()->resetShippingMethod();
             Mage::unregister('rule_data');
             try {
                 $this->_processQuote($orderData);
                 if (!empty($orderData['payment'])) {
                     $this->_getOrderCreateModel()->setPaymentData($orderData['payment']);
                     $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($orderData['payment']);
                 }

                 $address = $quote->getShippingAddress();

                 $address->collectShippingRates();
                 $rate = $address->getShippingRateByCode($profile->getShippingMethod());

                 Mage::dispatchEvent('mep_calculate_shipping_rate', array(
                     'product' => $this->_product, // Mage_Catalog_Model_Product
                     'quote' => $quote, // Mage_Sales_Model_Quote
                     'rate' => $rate // Mage_Sales_Model_Quote_Address_Rate
                     )
                 );

                 //$_order = $this->_getOrderCreateModel()
                 //    ->importPostData($orderData['order'])
                 //    ->createOrder();
                 $this->_getSession()->clear();
                 Mage::unregister('rule_data');
                 $quote->removeAllAddresses();
                 if ($rate == false) return false;
                 return $rate->getPrice();
             } catch (Exception $e) {
                 Mage::log("Order save error...".$e->getMessage());
             }
         }
     }


     public function setOrderInfo($payment_method, $country, $shipping_method)
     {
         $this->orderData = array(
             'session' => array(
                 'customer_id' => 0,
                 'store_id' => $this->_storeId,
             ),
             'payment' => array(
                 'method' => $payment_method,
             ),
             'add_products' => array(
                 $this->_product->getId() => array('qty' => 1),
             ),
             'order' => array(
                 'currency' => 'USD',
                 'account' => array(
                     'group_id' => $this->_groupId,
                     'email' => 'test@test.de'
                 ),
                 'billing_address' => array(
                     'prefix' => '',
                     'firstname' => 'Max',
                     'middlename' => '',
                     'lastname' => 'Mustermann',
                     'suffix' => '',
                     'company' => '',
                     'street' => array('Musterstrasse', ''),
                     'city' => 'Musterhausen',
                     'country_id' => $country,
                     'region' => '',
                     'region_id' => '',
                     'postcode' => '12345',
                     'telephone' => '012346789',
                     'fax' => '',
                 ),
                 'shipping_address' => array(
                     'prefix' => '',
                     'firstname' => 'Max',
                     'middlename' => '',
                     'lastname' => 'Mustermann',
                     'suffix' => '',
                     'company' => '',
                     'street' => array('Musterstrasse', ''),
                     'city' => 'Musterhausen',
                     'country_id' => $country,
                     'region' => '',
                     'region_id' => '',
                     'postcode' => '12345',
                     'telephone' => '012346789',
                     'fax' => '',
                 ),
                 'shipping_method' => $shipping_method,
                 'comment' => array(
                     'customer_note' => 'This order has been programmatically created via import script.',
                 )

             ),
         );
     }

     /**
      * Retrieve order create model
      *
      * @return  Mage_Adminhtml_Model_Sales_Order_Create
      */
     protected function _getOrderCreateModel()
     {
         return Mage::getSingleton('adminhtml/sales_order_create');
     }

     /**
      * Retrieve session object
      *
      * @return Mage_Adminhtml_Model_Session_Quote
      */
     protected function _getSession()
     {
         return Mage::getSingleton('adminhtml/session_quote');
     }

     /**
      * Initialize order creation session data
      *
      * @param array $data
      * @return Mage_Adminhtml_Sales_Order_CreateController
      */
     protected function _initSession($data)
     {
         /* Get/identify customer */
         if (!empty($data['customer_id'])) {
             $this->_getSession()->setCustomerId((int)$data['customer_id']);
         }
         /* Get/identify store */
         if (!empty($data['store_id'])) {
             $this->_getSession()->setStoreId((int)$data['store_id']);

         }
         return $this;
     }

     protected function _processQuote($data = array())
     {
         /* Saving order data */
         if (!empty($data['order'])) {
             $this->_getOrderCreateModel()->importPostData($data['order']);
         }
         $this->_getOrderCreateModel()->getQuote()->setCustomerIsGuest(1);

         $this->_getOrderCreateModel()->getQuote()->setWebsiteId($this->_websiteId);
         $this->_getOrderCreateModel()->getQuote()->setStoreId($this->_storeId);


         $this->_getOrderCreateModel()->getBillingAddress();
         $this->_getOrderCreateModel()->setShippingAsBilling(true);
         /* Just like adding products from Magento admin grid */
         if (!empty($data['add_products'])) {
             $this->_getOrderCreateModel()->addProducts($data['add_products']);
         }

         /* Add payment data */
         if (!empty($data['payment'])) {
             $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($data['payment']);
         }
         $this->_getOrderCreateModel()
             ->initRuleData()
         ->saveQuote();

         //$this->_getOrderCreateModel()->getQuote()->save();

         /* Collect shipping rates */
         $this->_getOrderCreateModel()->collectShippingRates();

         return $this;
     }

}
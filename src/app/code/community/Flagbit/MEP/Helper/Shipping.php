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

    public function emulateCheckout($item, $store_id, $profile)
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
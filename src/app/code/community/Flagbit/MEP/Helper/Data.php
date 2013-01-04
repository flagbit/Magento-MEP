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

class Flagbit_MEP_Helper_Data extends Mage_Core_Helper_Abstract
{

    private $_groupId = '0';
    private $_storeId = '1';
    private $orderData = array();

    /**
     * @param $profil_id
     * @param $template_id
     * @return bool
     */
    public function setTemplateProfil($profil_id, $template_id)
    {

        if ($profil_id > 0 && $template_id) {
            /* @var $transaction Mage_Core_Model_Resource_Transaction */
            $transaction = Mage::getResourceModel('core/transaction');
            $template_fields = array();

            if ($template_id == 1) {
                $template_fields[] = array('profile_id' => $profil_id,
                    'attribute_code' => 'sku',
                    'to_field' => 'Karl'
                );

                $template_fields[] = array('profile_id' => $profil_id,
                    'attribute_code' => 'name',
                    'to_field' => 'Karl2'
                );
            } elseif ($template_id == 2) {
                $template_fields[] = array('profile_id' => $profil_id,
                    'attribute_code' => 'sku',
                    'to_field' => 'Damian'
                );

                $template_fields[] = array('profile_id' => $profil_id,
                    'attribute_code' => 'name',
                    'to_field' => 'Damain2'
                );
            }

            foreach ($template_fields as $template) {
                /* @var $a Flagbit_MEP_Model_Mapping */
                $a = Mage::getModel('mep/mapping');
                $a->setData($template);
                $transaction->addObject($a);
            }


            try {
                $transaction->save();
                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * get current Profile Data
     *
     * @param bool $idOnly
     * @return array|null|string
     */
    public function getCurrentProfilData($idOnly = false)
    {
        if (Mage::getSingleton('adminhtml/session')->getMepProfileData()) {
            $data = Mage::getSingleton('adminhtml/session')->getMepProfileData();
        } elseif (Mage::registry('mep_profile_data')) {
            $data = Mage::registry('mep_profile_data')->getData();
        } else {
            $data = array();
        }
        if(is_bool($idOnly) && $idOnly === true){
            $data = isset($data['id']) ? $data['id'] : null;
        }elseif($idOnly){
            $data = isset($data[$idOnly]) ? $data[$idOnly] : '';
        }
        return $data;
    }

    /**
     * normalize strings (to use it as a variable name)
     *
     * @param array|string $mixed
     * @return mixed|null
     */
    public function normalizeVariableName($mixed)
    {
        $result = null;
        if (is_array($mixed)) {
            foreach ($mixed as &$value) {
                $value = $this->normalizeVariableName($value);
            }
            $result = $mixed;
        } else {
            $mixed = $this->normalize($mixed);
            $string = str_replace(array(' ', '-'), '_', $mixed);
            $result = preg_replace('([^A-Za-z0-9_]*)', '', $mixed);
        }
        return $result;
    }


    /**
     * normalize Characters
     * Example: ü -> ue
     *
     * @param string $string
     * @return string
     */
    public function normalize($string)
    {
        $table = array(
            'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
            'Õ' => 'O', 'Ö' => 'Oe', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'ae', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
            'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
            'ô' => 'o', 'õ' => 'o', 'ö' => 'oe', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b',
            'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r', 'ü' => 'ue',
        );

        return strtr($string, $table);
    }


    public function emulateCheckout($item, $store_id, $profil)
    {
        $this->_product = $item;
        //$this->_storeId = $store_id;
        $this->setOrderInfo($profil->getPaymentMethod(), $profil->getCountry(), $profil->getShippingMethod());
        $orderData = $this->orderData;
        if (!empty($orderData)) {
            $this->_initSession($orderData['session']);
            try {
                $this->_processQuote($orderData);
                if (!empty($orderData['payment'])) {
                    $this->_getOrderCreateModel()->setPaymentData($orderData['payment']);
                    $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($orderData['payment']);
                }
                $quote = $this->_getOrderCreateModel()->getQuote();
                $address = $quote->getShippingAddress();
                $rate = $address->getShippingRateByCode($profil->getShippingMethod());
                //$_order = $this->_getOrderCreateModel()
                //    ->importPostData($orderData['order'])
                //    ->createOrder();
                $this->_getSession()->clear();
                Mage::unregister('rule_data');
                if ($rate == false) return false;
                return $rate->getPrice();
            } catch (Exception $e) {
                Mage::log("Order save error...");
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
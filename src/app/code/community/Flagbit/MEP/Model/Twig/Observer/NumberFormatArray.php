<?php

class Flagbit_MEP_Model_Twig_Observer_NumberFormatArray {

    protected $_adapter;

    /**
     * Add a new filter to the the Twig instance
     *
     * @param Varien_Event_Observer $observer
     */
    public function addNumberFormatArray($observer) {
        $twig = $observer->getTwig();
        $policy = $observer->getPolicy();
        $this->_adapter = $observer->getAdapter();
        $policy->setAllowedFilter('number_format_array');
        $filter = new Twig_SimpleFilter('number_format_array', array($this, 'numberFormatArray'));
        $twig->addFilter($filter);
    }

    /**
     * Get a string of values separate by a delimiter to format given the formats parameters
     *
     * @param float $numbers
     * @param int $decimals
     * @param string $dec_point
     * @param string $thousands_sep
     * @return string
     */
    public function numberFormatArray($numbers , $decimals = 0 , $dec_point = '.' , $thousands_sep = ',') {
        $delimiter = $this->_adapter->getConfigurableDelimiter();
        $numbersArray = explode($delimiter, $numbers);
        if (is_array($numbersArray)) {
            foreach ($numbersArray as &$number) {
                $number = floatval($number);
                $number = number_format($number, $decimals, $dec_point, $thousands_sep);
            }
            $numbers = implode($delimiter, $numbersArray);
        }
        return $numbers;
    }
}
<?php

class Flagbit_MEP_Model_Twig_Observer_NumberFormatArray {

    protected $_adapter;

    /**
     * @param Varien_Event_Observer $args
     *
     * Add a new filter to the the Twig instance
     */
    public function addNumberFormatArray($args) {
        $twig = $args->getTwig();
        $policy = $args->getPolicy();
        $this->_adapter = $args->getAdapter();
        $policy->setAllowedFilter('number_format_array');
        $filter = new Twig_SimpleFilter('number_format_array', array($this, 'numberFormatArray'));
        $twig->addFilter($filter);
    }

    /**
     * @param float $numbers
     * @param int $decimals
     * @param string $dec_point
     * @param string $thousands_sep
     * @return string
     *
     * Get a string of values separate by a delimiter to format given the formats parameters
     */
    public function numberFormatArray($numbers , $decimals = 0 , $dec_point = '.' , $thousands_sep = ',') {
        $delimiter = $this->_adapter->getConfigurableDelimiter();
        $numbersArray = explode($delimiter, $numbers);
        if (is_array($numbersArray)) {
            foreach ($numbersArray as &$number) {
                $number = number_format($number, $decimals, $dec_point, $thousands_sep);
            }
            $numbers = implode($delimiter, $numbersArray);
        }
        return $numbers;
    }
}
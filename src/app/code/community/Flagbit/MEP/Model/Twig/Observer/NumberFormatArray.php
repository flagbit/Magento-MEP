<?php

class Flagbit_MEP_Model_Twig_Observer_NumberFormatArray extends Varien_Object {

    protected $_adapter;

    public function addNumberFormatArray($args) {
        $twig = $args->getTwig();
        $policy = $args->getPolicy();
        $this->_adapter = $args->getAdapter();
        $policy->setAllowedFilter('number_format_array');
        $filter = new Twig_SimpleFilter('number_format_array', array($this, 'numberFormatArray'));
        $twig->addFilter($filter);
    }

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
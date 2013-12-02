<?php

class Flagbit_MEP_Model_Twig_Observer_SortArray extends Varien_Object {

    protected $_adapter;

    public function addSortArray($args) {
        $twig = $args->getTwig();
        $policy = $args->getPolicy();
        $this->_adapter = $args->getAdapter();
        $policy->setAllowedFilter('sort_array');
        $filter = new Twig_SimpleFilter('sort_array', array($this, 'sortArray'));
        $twig->addFilter($filter);
    }

    public function sortArray($values, $order) {
        $delimiter = $this->_adapter->getConfigurableDelimiter();
        $valuesArray = explode($delimiter, $values);
        if (is_array($valuesArray)) {
            if ($order == 'asc') {
                sort($valuesArray);
            }
            elseif ($order == 'desc') {
                rsort($valuesArray);
            }
            $values = implode($delimiter, $valuesArray);
        }
        return $values;
    }
}
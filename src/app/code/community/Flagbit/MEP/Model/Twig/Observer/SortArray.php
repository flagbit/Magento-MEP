<?php

class Flagbit_MEP_Model_Twig_Observer_SortArray {

    protected $_adapter;

    /**
     * @param Varien_Event_Observer $args
     *
     * Add a new filter to the the Twig instance
     */
    public function addSortArray($args) {
        $twig = $args->getTwig();
        $policy = $args->getPolicy();
        $this->_adapter = $args->getAdapter();
        $policy->setAllowedFilter('sort_array');
        $filter = new Twig_SimpleFilter('sort_array', array($this, 'sortArray'));
        $twig->addFilter($filter);
    }

    /**
     * @param string $values
     * @param string $order
     * @return string
     *
     * Get a string of values separate by a delimiter to sort given the parameter order
     */
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
<?php

class Flagbit_MEP_Model_Twig_Observer_SortArray {

    protected $_adapter;

    /**
     * Add a new filter to the the Twig instance
     *
     * @param Varien_Event_Observer $observer
     */
    public function addSortArray($observer) {
        $twig = $observer->getTwig();
        $policy = $observer->getPolicy();
        $this->_adapter = $observer->getAdapter();
        $policy->setAllowedFilter('sort_array');
        $filter = new Twig_SimpleFilter('sort_array', array($this, 'sortArray'));
        $twig->addFilter($filter);
    }

    /**
     * Get a string of values separate by a delimiter to sort given the parameter order
     *
     * @param string $values
     * @param string $order
     * @return string
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
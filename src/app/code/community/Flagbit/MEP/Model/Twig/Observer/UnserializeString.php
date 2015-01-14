<?php

class Flagbit_MEP_Model_Twig_Observer_UnserializeString {

    protected $_adapter;

    /**
     * Add a new filter to the the Twig instance
     *
     * @param Varien_Event_Observer $observer
     */
    public function addUnserializeString($observer) {
        $twig = $observer->getTwig();
        $policy = $observer->getPolicy();
        $this->_adapter = $observer->getAdapter();
        $policy->setAllowedFilter('unserialize_string');
        $filter = new Twig_SimpleFilter('unserialize_string', array($this, 'unserializeString'));
        $twig->addFilter($filter);
    }

    /**
     * Get a string of values separate by a delimiter to format given the formats parameters
     *
     * @param float $numbers
     */
    public function unserializeString($string) {
        $result = null;
        try {
            $result = unserialize($string);

        } catch(Exception $e) {
            $result = false;
        }
        return $result;
    }
}

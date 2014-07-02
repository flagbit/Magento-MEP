<?php

class Flagbit_MEP_Block_Adminhtml_Shipping_Popup
    extends Mage_Core_Block_Template
{

    protected $_selectAttributeCodes = array();

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changing layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        // Save button
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'type'  => 'submit',
                    'value' => 'Submit',
                    'class' => 'save',
                    'title' => 'Submit',
                    'label' => Mage::helper('catalog')->__('Save')
                ))
        );

        return parent::_prepareLayout();
    }

    public function getProfileId()
    {
        return $this->getRequest()->getParam('profile_id', null);
    }

    public function getMapping()
    {
        return Mage::getModel('mep/shipping_attribute')->load($this->getRequest()->getParam('id'));
    }

    /**
     * Return html code of the save button for popup window
     *
     * @return string
     */
    public function getSaveButtonHtml() {
        return $this->getChildHtml('save_button');
    }
}
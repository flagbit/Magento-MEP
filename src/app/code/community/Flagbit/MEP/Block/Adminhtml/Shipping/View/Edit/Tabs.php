<?php

class Flagbit_MEP_Block_Adminhtml_Shipping_View_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Class Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('rule_tabs');
        $this->setDestElementId('edit_form');
    }

    /**
     * _beforeToHtml
     *
     * Adds the tabs
     *
     * @see Mage_Adminhtml_Block_Widget_Tabs::_beforeToHtml()
     *
     * @return Flagbit_MEP_Block_Adminhtml_Profil_View_Edit_Tabs Self.
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label' => Mage::helper('mep')->__('General Information'),
            'title' => Mage::helper('mep')->__('General Information'),
            'content' => $this->getLayout()->createBlock('mep/adminhtml_shipping_view_edit_tab_general')->toHtml(),
        ));

        $this->addTab('mapping', array(
            'label' => Mage::helper('mep')->__('Shipping Mapping'),
            'content' => $this->getLayout()->createBlock('mep/adminhtml_shipping_view_mapping')->toHtml(),
        ));

        $this->setActiveTab($this->getRequest()->get('tab'));

        return parent::_beforeToHtml();
    }
}

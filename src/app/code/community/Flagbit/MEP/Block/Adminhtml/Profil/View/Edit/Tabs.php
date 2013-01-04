<?php

class Flagbit_MEP_Block_Adminhtml_Profil_View_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
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
        $data = Mage::helper('mep')->getCurrentProfilData();

        $this->addTab('form_section', array(
            'label' => Mage::helper('mep')->__('General Information'),
            'title' => Mage::helper('mep')->__('General Information'),
            'content' => $this->getLayout()->createBlock('mep/adminhtml_profil_view_edit_tab_general')->toHtml(),
        ));

        $this->addTab('mapping', array(
            'label' => Mage::helper('mep')->__('Field Mapping'),
            'title' => Mage::helper('mep')->__('Field Mapping'),
            'content' => $this->getLayout()->createBlock('mep/adminhtml_profil_view_mapping')->toHtml(),
            'url' => $this->getUrl('*/profil_attribute/index', array('profile_id' => $this->getRequest()->getParam('id'),'shipping_id' => $data["shipping_id"])),
            'class' => 'ajax',
        ));

        $this->addTab('form_data_format', array(
            'label' => Mage::helper('mep')->__('Data Format'),
            'title' => Mage::helper('mep')->__('Data Format'),
            'content' => $this->getLayout()->createBlock('mep/adminhtml_profil_view_edit_tab_format')->toHtml(),
        ));

        if(!empty($data['use_twig_templates'])){
            $this->addTab('form_data_xslt', array(
                'label' => Mage::helper('mep')->__('Template'),
                'title' => Mage::helper('mep')->__('Template'),
                'content' => $this->getLayout()->createBlock('mep/adminhtml_profil_view_edit_tab_template')->toHtml(),
            ));
        }

        $this->addTab('form_export_filters', array(
            'label' => Mage::helper('mep')->__('Export Filters'),
            'title' => Mage::helper('mep')->__('Export Filters'),
            'content' => $this->getLayout()->createBlock('mep/adminhtml_category_dynamic')->toHtml(),
        ));

        $this->setActiveTab($this->getRequest()->get('tab'));

        return parent::_beforeToHtml();
    }
}

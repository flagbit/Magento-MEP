<?php

class Flagbit_MEP_Block_Adminhtml_Export_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form before rendering HTML.
     *
     * @return Mage_ImportExport_Block_Adminhtml_Export_Edit_Form
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('mep');
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/getFilter'),
            'method' => 'post'
        ));
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $helper->__('Export Settings')));
        $fieldset->addField('entity', 'select', array(
            'name' => 'entity',
            'title' => $helper->__('Entity Type'),
            'label' => $helper->__('Entity Type'),
            'required' => false,
            'onchange' => 'editForm.getFilter();',
            'values' => Mage::getModel('importexport/source_export_entity')->toOptionArray()
        ));
        $fieldset->addField('file_format', 'select', array(
            'name' => 'file_format',
            'title' => $helper->__('Export File Format'),
            'label' => $helper->__('Export File Format'),
            'required' => false,
            'values' => Mage::getModel('importexport/source_export_format')->toOptionArray()
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

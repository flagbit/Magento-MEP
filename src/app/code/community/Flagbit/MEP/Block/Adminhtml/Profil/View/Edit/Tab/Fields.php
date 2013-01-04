<?php
class Flagbit_MEP_Block_Adminhtml_Profil_View_Edit_Tab_Fields extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * _prepareForm
     *
     * Prepares the edit form
     *
     * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
     *
     * @return Flagbit_MEP_Block_Adminhtml_View_Edit_Tab_General Self.
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'mep_fieldmapping_form',
            array(
                'legend' => Mage::helper('mep')->__('Field Mapping')
            )
        );

        $form->setValues(Mage::helper('mep')->getCurrentProfileData());
        return parent::_prepareForm();
    }
}
<?php

class Flagbit_PSExport_Block_System_Convert_Gui_Edit_Tab_Generator extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_view');

        $model = Mage::registry('current_convert_profile');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'=>Mage::helper('adminhtml')->__('Profile Generator'),
            'class'=>'fieldset-wide'
        ));
       
        $fieldset->addField('template', 'select', array(
            'name' => 'template',
            'label' => Mage::helper('adminhtml')->__('Template'),
            'title' => Mage::helper('adminhtml')->__('Template'),
        	'options' => array('_new_' => $this->__('No Template')) + Mage::getModel('flagbit_psexport/profile_template')->toOptionArray()

        ));

        $form->setValues($model->getData());

        $this->setForm($form);

        return $this;
    }

}


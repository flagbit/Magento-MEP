<?php

class Flagbit_PSExport_Block_System_Convert_Gui_Edit_Tab_Schedule extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_view');

        $model = Mage::registry('current_convert_profile');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'=>Mage::helper('adminhtml')->__('Schedule Settings'),
            'class'=>'fieldset-wide'
        ));

        $fieldset->addField('schedule', 'select', array(
            'name' => 'schedule',
            'label' => Mage::helper('adminhtml')->__('Interval'),
            'title' => Mage::helper('adminhtml')->__('Interval'),
        	'options' => array(
        		''			=> $this->__('disabled'),
        		'minutely'	=> $this->__('minutely'),
        		'daily'		=> $this->__('dialy'),
        		'weekly'	=> $this->__('weekly'),
        		'monthly'	=> $this->__('monthly')
        	)

        ));

        $form->setValues($model->getData());

        $this->setForm($form);

        return $this;
    }

}


<?php
class Flagbit_MEP_Block_Adminhtml_Profile_View_Edit_Tab_Template extends Mage_Adminhtml_Block_Widget_Form
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
            'mep_data_xslt_form',
            array(
                'legend' => Mage::helper('mep')->__('Template')
            )
        );

        $fieldset->addField(
            'twig_header_template',
            'textarea',
            array(
                'label' => Mage::helper('mep')->__('Header Template'),
                'style' => 'width: 600px; height: 200px',
                'name' => 'twig_header_template',
            )
        );

        $fieldset->addField(
            'twig_content_template',
            'textarea',
            array(
                'label' => Mage::helper('mep')->__('Content Template'),
                'style' => 'width: 600px; height: 200px',
                'name' => 'twig_content_template',
            )
        );

        $fieldset->addField(
            'twig_footer_template',
            'textarea',
            array(
                'label' => Mage::helper('mep')->__('Footer Template'),
                'style' => 'width: 600px; height: 200px',
                'name' => 'twig_footer_template',
            )
        );
        $form->setValues(Mage::helper('mep')->getCurrentProfileData());

    }

}
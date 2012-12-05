<?php
class Flagbit_MEP_Block_Adminhtml_Profil_View_Edit_Tab_Xslt extends Mage_Adminhtml_Block_Widget_Form
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
        if (Mage::getSingleton('adminhtml/session')->getMepProfileData()) {
            $data = Mage::getSingleton('adminhtml/session')->getMepProfileData();
            Mage::getSingleton('adminhtml/session')->setMepProfileData(null);
        } elseif (Mage::registry('mep_profile_data')) {
            $data = Mage::registry('mep_profile_data')->getData();
        } else {
            $data = array();
        }

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'mep_data_xslt_form',
            array(
                'legend' => Mage::helper('mep')->__('XSLT')
            )
        );

        $loader = new Twig_Loader_String();
        $twig = new Twig_Environment($loader);


        $fieldset->addField(
            'xslt',
            'textarea',
            array(
                'label' => Mage::helper('mep')->__('XSLT').$twig->render('Hello {{ name }}!', array('name' => 'Fabien')),
                //'class'    => 'required-entry',
                //'required' => true,
                'style' => 'width: 600px; height: 400px',
                'name' => 'xslt',
            )
        );


    }

}
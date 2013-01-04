<?php

class Flagbit_MEP_Block_Adminhtml_Attribute_View_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * _prepareForm
     *
     * Prepares the form
     *
     * @return Flagbit_MEP_Block_Adminhtml_Profil_View_Edit_Form Self.
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ));

        $fieldset = $form->addFieldset(
            'mep_profile_form',
            array(
                'legend' => Mage::helper('mep')->__('General')
            )
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                'label' => Mage::helper('mep')->__('Attribute Mapping Name'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'name',
            )
        );

        $fieldset->addField(
            'attribute_code',
            'text',
            array(
                'label' => Mage::helper('mep')->__('Attribute Code'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'attribute_code',
            )
        );

        $fieldset->addField(
            'source_attribute_code',
            'select',
            array(
                'label' => Mage::helper('mep')->__('Attribute'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'source_attribute_code',
                'values'    => Mage::getSingleton('mep/attribute_mapping')->getValuesForForm(),
            )
        );

        $form->setUseContainer(false);

        $data = array();
        if($this->getRequest()->getPost()){
            $data = $this->getRequest()->getPost();
        }elseif(Mage::registry('mep_attribute_mapping') instanceof Flagbit_MEP_Model_Attribute_Mapping){
            $data = Mage::registry('mep_attribute_mapping')->getData();
        }

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }


    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html  = '<form '.$this->getForm()->serialize($this->getForm()->getHtmlAttributes()).'>';
        $html .= '<input name="form_key" type="hidden" value="'.Mage::getSingleton('core/session')->getFormKey().'" />';
        $html .= parent::_toHtml();
        $html .= $this->getLayout()->createBlock('mep/adminhtml_attribute_view_edit_options')->toHtml();
        $html .= '</form>';
        return $html;
    }

}
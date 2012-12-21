<?php
class Flagbit_MEP_Block_Adminhtml_Shipping_View_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
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
        } elseif (Mage::registry('mep_shipping_data')) {
            $data = Mage::registry('mep_shipping_data')->getData();
        } else {
            $data = array();
        }

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'mep_profile_form',
            array(
                'legend' => Mage::helper('mep')->__('Profil')
            )
        );
        $fieldset->addField(
            'id',
            'label',
            array(
                'label' => Mage::helper('mep')->__('Profile ID'),
                'name' => 'id',
            )
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                'label' => Mage::helper('mep')->__('Profile Name'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'name',
            )
        );

        $fieldset->addField(
            'status',
            'select',
            array(
                'label' => Mage::helper('mep')->__('Status'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'status',
                'options' => $this->_getStatusOptionsHash()
            )
        );

        $form->setValues($data);
        return parent::_prepareForm();
    }


    protected function _getStatusOptionsHash()
    {
        $options = array(
            0 => Mage::helper('mep')->__('Disable'),
            1 => Mage::helper('mep')->__('Enable'),
        );
        return $options;
    }

}
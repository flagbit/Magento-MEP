<?php
class Flagbit_MEP_Block_Adminhtml_Profile_View_Edit_Tab_Format extends Mage_Adminhtml_Block_Widget_Form
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
            'mep_data_format_form',
            array(
                'legend' => Mage::helper('mep')->__('Data Format')
            )
        );

        $fieldset->addField(
            'dataformat',
            'select',
            array(
                'label' => Mage::helper('mep')->__('Type'),
                'name' => 'dataformat',
                'options' => $this->_getDataFormatOptionsHash(),
                'note' => "only csv"
            )
        );

        $fieldset->addField(
            'use_twig_templates',
            'select',
            array(
                'label' => Mage::helper('mep')->__('Use Templates'),
                'name' => 'use_twig_templates',
                'options' => $this->_getYesNoOptionsHash(),
                'note' => "only for experienced user"
            )
        );

        $fieldset->addField(
            'delimiter',
            'text',
            array(
                'label' => Mage::helper('mep')->__('Value delimiter'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'delimiter',
                'note' => 'Use \t to use TAB as delimiter.'
            )
        );

        $fieldset->addField(
            'enclose',
            'text',
            array(
                'label' => Mage::helper('mep')->__('Enclose values in'),
                'name' => 'enclose',
            )
        );

        $fieldset->addField(
            'originalrow',
            'select',
            array(
                'label' => Mage::helper('mep')->__('Skip Header'),
                'name' => 'originalrow',
                'options' => $this->_getYesNoOptionsHash()
            )
        );

        $fieldset->addField('filename', 'text', array(
            'label' => Mage::helper('mep')->__('Name of the file'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'filename',
        ));

        $fieldset->addField('filepath', 'text', array(
            'label' => Mage::helper('mep')->__('Path to export'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'filepath',
            'note' => 'Path relative to document root'
        ));

        $fieldset->addField('category_delimiter', 'text', array(
            'label' => Mage::helper('mep')->__('Separator between categories'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'category_delimiter'
        ));

        $fieldset->addField('configurable_value_delimiter', 'text', array(
            'label' => Mage::helper('mep')->__('Separator between configurable values'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'configurable_value_delimiter'
        ));

        $fieldset->addField('profile_locale', 'text', array(
            'label' => Mage::helper('mep')->__('Change default locale'),
            'name' => 'profile_locale'
        ));

        $fieldset->addField(
            'shipping_id',
            'select',
            array(
                'label' => Mage::helper('mep')->__('Shipping Profil'),
                'name' => 'shipping_id',
                'options' => $this->_getShippingOptionsHash(),
            )
        );

        $form->setValues(Mage::helper('mep')->getCurrentProfileData());



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


    protected function _getYesNoOptionsHash()
    {
        $options = array(
            0 => Mage::helper('mep')->__('No'),
            1 => Mage::helper('mep')->__('Yes'),
        );
        return $options;
    }

    protected function _getDataFormatOptionsHash()
    {
        $options = array(
            0 => Mage::helper('mep')->__('CSV'),
            //1 => Mage::helper('mep')->__('XML'),
        );
        return $options;
    }

    protected function _getShippingOptionsHash()
    {
        $options = array(
            0 => Mage::helper('mep')->__('None'),
        );

        $collection = Mage::getModel('mep/shipping')->getCollection()->addFieldToFilter('status', 1);
        foreach($collection as $item) {
            $options[$item->getId()] = $item->getName();
        }
        return $options;
    }




}

<?php
class Flagbit_MEP_Block_Adminhtml_Profil_View_Edit_Tab_Format extends Mage_Adminhtml_Block_Widget_Form
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
		}
		else {
			$data = array();
		}

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
        	'text',
            array(
				'label'    => Mage::helper('mep')->__('Type'),
				'name'     => 'dataformat',
		    )
        );

		$fieldset->addField(
			'delimiter',
			'text',
		    array(
				'label'    => Mage::helper('mep')->__('Value Delimiter'),
                //'class'    => 'required-entry',
                //'required' => true,
                'name'     => 'delimiter',
		    )
        );


        $fieldset->addField(
            'status',
            'select',
            array(
                'label'    => Mage::helper('mep')->__('Enclose Values In:'),
                //'class'    => 'required-entry',
                //'required' => true,
                'name'     => 'status',
                'options'	=> $this->_getStatusOptionsHash()
            )
        );

        $fieldset->addField(
            'originalrow',
            'text',
            array(
                'label'    => Mage::helper('mep')->__('Original Magento attributenames in first row'),
                //'class'    => 'required-entry',
                //'required' => true,
                'name'     => 'originalrow',
            )
        );


        $fieldset->addField(
            'export',
            'text',
            array(
                'label'    => Mage::helper('mep')->__('Export'),
                //'class'    => 'required-entry',
                //'required' => true,
                'name'     => 'export',
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
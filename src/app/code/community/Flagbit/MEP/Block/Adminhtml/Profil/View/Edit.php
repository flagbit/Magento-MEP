<?php

class Flagbit_MEP_Block_Adminhtml_Profil_View_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	/**
     * Class Constructor
     * 
     * @return void
     */
	public function __construct()
	{
		parent::__construct();
        $this->_objectId = 'id';
		$this->_blockGroup = 'billings';
		$this->_controller = 'adminhtml_billings_view';
		$this->_mode = 'edit';

		$this->_updateButton('save', 'label', Mage::helper('billings')->__('Save'));
		$this->_updateButton('delete', 'label', Mage::helper('billings')->__('Delete'));

		$this->_addButton('save_and_continue', array(
				'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
				'onclick' => 'saveAndContinueEdit()',
				'class' => 'save',
		), -100);

        $this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('form_content') == null) {
					tinyMCE.execCommand('mceAddControl', false, 'edit_form');
				} else {
					tinyMCE.execCommand('mceRemoveControl', false, 'edit_form');
				}
			}

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
    }
	
	/**
     * getHeaderText
     * 
     * Returns the headline for the edit form
     * 
     * @see Mage_Adminhtml_Block_Widget_Container::getHeaderText()
     * 
     * @return string Headline
     */
	public function getHeaderText()
	{
		if (Mage::registry('proselma_billings_data') && Mage::registry('proselma_billings_data')->getId())
		{
			return Mage::helper('billings')->__('Edit Billing "%s"', $this->htmlEscape(Mage::registry('proselma_billings_data')->getName()));
		}
		else
		{
			return Mage::helper('billings')->__('New Billing');
		}
	}
}
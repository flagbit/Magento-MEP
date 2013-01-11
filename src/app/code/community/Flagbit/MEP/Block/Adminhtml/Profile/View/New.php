<?php

class Flagbit_MEP_Block_Adminhtml_Profile_View_New extends Mage_Adminhtml_Block_Widget_Form_Container
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
        $this->_blockGroup = 'mep';
        $this->_controller = 'adminhtml_profile_view';
        $this->_mode = 'new';

        $this->_updateButton('save', 'label', Mage::helper('mep')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('mep')->__('Delete'));

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
        if (Mage::helper('mep')->getCurrentProfileData(true)) {
            return Mage::helper('mep')->__('Edit Profile "%s"', $this->htmlEscape(Mage::helper('mep')->getCurrentProfileData('name')));
        } else {
            return Mage::helper('mep')->__('New Profile');
        }
    }
}
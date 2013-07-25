<?php

class Flagbit_MEP_Block_Adminhtml_Profile_View_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected $_profileTabId = 'profile_tabs';

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
        $this->_mode = 'edit';

        $this->_updateButton('save', 'label', Mage::helper('mep')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('mep')->__('Delete'));

        $this->_addButton('save_and_continue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $profile_id = Mage::helper('mep')->getCurrentProfileData(true);
        $this->_addButton('Run', array(
            'label' => Mage::helper('adminhtml')->__('Preview'),
            'onclick' => "mepPreviewDialog.openDialog('".$this->getUrl('*/*/runClick', array('id' => $profile_id))."')",
            'class' => 'go',
        ), -1, 5);


        if (! empty($profile_id)) {
                $this->_addButton('duplicate', array(
                    'label' => Mage::helper('adminhtml')->__('Duplicate'),
                    'onclick' => "$('edit_form').action += 'duplicate/true/'; editForm.submit();",
                    'class' => 'scalable add',
                ), 0);
        }

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                activeTab = null;
                {$this->_profileTabId}JsTabs.tabs.each(function(elem, index){
                    if(elem.hasClassName('active')){
                        activeTab = elem.id.substring('{$this->_profileTabId}'.length + 1);
                    }
                });
                editForm.submit($('edit_form').action+'back/edit/tab/'+activeTab);
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
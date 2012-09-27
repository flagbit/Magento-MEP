<?php
 
class Flagbit_PSExport_Block_System_Convert_Gui_Edit_Tabs extends Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tabs {
	
	
    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setCanLoadExtJs(true);
        }
        return parent::_prepareLayout();
    }	
	
    protected function _beforeToHtml()
    {
        $profile = Mage::registry('current_convert_profile');

        $wizardBlock = $this->getLayout()->createBlock('flagbit_psexport/system_convert_gui_edit_tab_wizard');
        $wizardBlock->addData($profile->getData());

        $new = !$profile->getId();
        $template= $this->getRequest()->getParam('template', false);
		
        if($new){
        	
	        $this->addTab('generator', array(
	            'label'     => Mage::helper('adminhtml')->__('Profile Generator'),
	            'content'   => $this->getLayout()->createBlock('flagbit_psexport/system_convert_gui_edit_tab_generator')->initForm()->toHtml(),
	            'active'    => true,
	        ));      
	        
        }
        if(!$new or ($new && $template)){ 
        
	        $this->addTab('wizard', array(
	            'label'     => Mage::helper('adminhtml')->__('Profile Wizard'),
	            'content'   => $wizardBlock->toHtml(),
	            'active'    => true,
	        ));
        }
        if (!$new) {
            if ($profile->getDirection()!='export') {
                $this->addTab('upload', array(
                    'label'     => Mage::helper('adminhtml')->__('Upload File'),
                    'content'   => $this->getLayout()->createBlock('adminhtml/system_convert_gui_edit_tab_upload')->toHtml(),
                ));
            }

            $this->addTab('run', array(
                'label'     => Mage::helper('adminhtml')->__('Run Profile'),
                'content'   => $this->getLayout()->createBlock('adminhtml/system_convert_profile_edit_tab_run')->toHtml(),
            ));

            $this->addTab('view', array(
                'label'     => Mage::helper('adminhtml')->__('Profile Actions XML'),
                'content'   => $this->getLayout()->createBlock('adminhtml/system_convert_gui_edit_tab_view')->initForm()->toHtml(),
            ));

            if ($profile->getDirection()=='export') {
        	
		        $this->addTab('test', array(
		            'label'     => Mage::helper('adminhtml')->__('Schedule'),
		            'content'   => $this->getLayout()->createBlock('flagbit_psexport/system_convert_gui_edit_tab_schedule')->initForm()->toHtml(),
		        ));
        	}            
            
            $this->addTab('history', array(
                'label'     => Mage::helper('adminhtml')->__('Profile History'),
                'content'   => $this->getLayout()->createBlock('adminhtml/system_convert_profile_edit_tab_history')->toHtml(),
            ));
        }

        return Mage_Adminhtml_Block_Widget_Tabs::_beforeToHtml();
    }	

	
}
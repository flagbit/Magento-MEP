<?php

class Flagbit_PSExport_Model_Observer
{

    public function profileSave(Varien_Event_Observer $observer)
    {
    	/*@var $controller Mage_Adminhtml_System_Convert_GuiController */
        $controller = $observer->getEvent()->getControllerAction();
        
        if($controller->getRequest()->getParam('duplicate')){
        	
	        $profileId = (int) $controller->getRequest()->getParam('profile_id');
	        
	        /*@var $profile Mage_Dataflow_Model_Profile */
	        $profile = Mage::getModel('dataflow/profile');
	
	        if ($profileId) {
	            $profile->load($profileId);
	            if (!$profile->getId()) {
	                Mage::getSingleton('adminhtml/session')->addError('The profile you are trying to duplicate no longer exists');
					$this->_redirect('*/*/index');
	                return false;
	            }
	            
	            $newProfile = $profile->duplicate();
	            $this->_redirect('*/*/edit', array('id' => $newProfile->getId()));
	        }
        	

        // handle Profile Templates	
        }else{
        	
	        $template = $controller->getRequest()->getPost('template');
	      
	        switch($template){
	        	
	        	case null;
	        		break;
	        	
	        	case "_new_":
	        		if($controller->getRequest()->getPost('name')){
						$this->_redirect('*/*/edit', array('template' => '_new_'));
	        		}
	        		break;
	        		
	        	case ($templateDef = Mage::getModel('flagbit_psexport/profile_template')->load($template)) !== null:
	        		$templateDef['form_key'] = $controller->getRequest()->getPost('form_key');
	        		$controller->getRequest()->setPost($templateDef);
	        		break;
	        }
        }
        return $this;
    }

	protected function _redirect($url, $params=array()){
		
        Mage::app()->getResponse()->setRedirect(
        	Mage::helper('adminhtml')->getUrl($url, $params)
        );
        Mage::app()->getResponse()->sendResponse();
        die();	
	}
}

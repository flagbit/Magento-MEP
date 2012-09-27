<?php

class Flagbit_PSExport_Model_Profile_History extends Mage_Dataflow_Model_Profile_History
{
    protected function _construct()
    {
        $this->_init('dataflow/profile_history');
    }

    protected function _beforeSave()
    {
        if (!$this->getProfileId()) {
            $profile = Mage::registry('current_convert_profile');
            if ($profile) {
                $this->setProfileId($profile->getId());
            }
        }
        if (!$this->getUserId()) {
        	if(Mage::getSingleton('admin/session')->getUser() instanceof Mage_Admin_Model_User){
            	$this->setUserId(Mage::getSingleton('admin/session')->getUser()->getId());
        	}else{
        		$lastHistory = Mage::getResourceModel('dataflow/profile_history_collection')
        				->addFieldToFilter('profile_id', array('eq' => $this->getProfileId()))
        				->setOrder('performed_at', 'DESC')
        				->getFirstItem();
        		
        		$this->setUserId($lastHistory->getUserId());		
        	}
        }

        Mage_Core_Model_Abstract::_beforeSave();
        return $this;
    }
}

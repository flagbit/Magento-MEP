<?php

class Flagbit_MEP_Block_Adminhtml_Profil_Popup
    extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getProfileId()
    {
        return $this->getRequest()->getParam('profile_id', null);
    }

    public function getMapping()
    {
        return Mage::getModel('mep/mapping')->load($this->getRequest()->getParam('id'));
    }

}
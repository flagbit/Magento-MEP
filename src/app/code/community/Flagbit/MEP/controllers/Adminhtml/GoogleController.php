<?php

class   Flagbit_MEP_Adminhtml_GoogleController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function loadcategoriesAction()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        if ($storeId)
        {
            Mage::register('category_store_id', $storeId);
        }
        $this->loadLayout();
        $this->renderLayout();
    }
}
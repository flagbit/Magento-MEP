<?php

class Flagbit_MEP_Adminhtml_ProfilController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * _initAction
     * 
     * @return Flagbit_MEP_Adminhtml_ProfilController Self;
     */
    protected function _initAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('mep/profil');
        return $this;
    }

    /**
     * indexAction
     * 
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * editAction
     * 
     * @return void
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('mep/profil')->load((int) $id);

        if ($model->getId() || !$id) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if ($data) {
                $model->setData($data)->setId($id);
            }
            else{
                Mage::getSingleton('adminhtml/session')->setMepProfileData($model->getData());
            }

            Mage::register('mep_profil_data', $model);

            $this->_initAction();
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('mep/adminhtml_profil_view_edit'));
            $this->_addLeft($this->getLayout()->createBlock('mep/adminhtml_profil_view_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mep')->__('Profil does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * saveAction
     * 
     * @return void
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('mep/profil');

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->setId($id);
            }

            Mage::getSingleton('adminhtml/session')->setFormData($data);

            try {
                $model->setData($data);
                if ($id) {
                    $model->setId($id);
                }

                $model->save();

                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('mep')->__('Error saving billing'));
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mep')->__('Profil was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mep')->__('No data found to save'));
        $this->_redirect('*/*/');
    }

    /**
     * deleteAction
     * 
     * @return void
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                Mage::getModel('mep/profil')->load($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mep')->__('successfully deleted'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * massDeleteAction
     * 
     * @return void
     */
    public function massDeleteAction()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            $this->_getSession()->addError($this->__('Please select product(s).'));
        }
        else {
            try {
                foreach ($productIds as $productId) {
                    Mage::getModel('mep/profil')->load($productId)->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d profil(s) have been deleted.', count($productIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function fieldsAction(){
        $this->loadLayout();
        $this->getLayout()->getBlock('fields.grid');
            //->setProfile($this->getRequest()->getPost('profile', null));
        $this->renderLayout();
    }




}
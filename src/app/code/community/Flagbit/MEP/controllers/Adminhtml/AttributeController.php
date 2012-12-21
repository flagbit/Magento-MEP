<?php

class Flagbit_MEP_Adminhtml_AttributeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * _initAction
     *
     * @return Flagbit_MEP_Adminhtml_AttributeController Self;
     */
    protected function _initAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('system/mep/attribute');
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

    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * editAction
     *
     * @return void
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('mep/attribute_mapping')->load((int)$id);

        if ($model->getId() || !$id) {
            Mage::register('mep_attribute_mapping', $model);
            $this->_initAction();
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->renderLayout();

            Mage::getSingleton('adminhtml/session')->setMepProfileData(null);
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mep')->__('Attribute Mapping does not exist'));
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
        $session = Mage::getSingleton('adminhtml/session');
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('mep/attribute_mapping');
            $id = $this->getRequest()->getParam('id');

            $data['id'] = $id;
            if ($id) {
                $model->load($id);
            }

            try {
                $model->setData($data);
                $model->save();

                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('mep')->__('Error saving Attribute Mapping'));
                }

                $session->addSuccess(Mage::helper('mep')->__('Attribute Mapping was successfully saved'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }
            return;
        }
        $session->addError(Mage::helper('mep')->__('No data found to save'));
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
                Mage::getModel('mep/attribute_mapping')->load($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mep')->__('successfully deleted'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
}

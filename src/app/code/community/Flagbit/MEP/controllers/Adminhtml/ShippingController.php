<?php

class Flagbit_MEP_Adminhtml_ShippingController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Initialize layout.
     *
     * @return Mage_ImportExport_Adminhtml_ExportController
     */
    protected function _initAction()
    {
        $this->_title($this->__('Import/Export'))
            ->loadLayout()
            ->_setActiveMenu('system/importexport');

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
        Mage::getSingleton('adminhtml/session')->setMepProfileData(null);
        $this->_forward('edit');
    }

    /**
     * indexAction
     *
     * @return void
     */
    public function popupAction()
    {

        $this->loadLayout('empty')->renderLayout();
        $html = $this->getLayout()->createBlock('mep/adminhtml_shipping_popup')->setTemplate('mep/shipping/popup.phtml')->toHtml();
        $this->getResponse()->setBody($html);
    }

    /**
     * editAction
     *
     * @return void
     */
    public function editAction()
    {
        $this->_initAction();
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('mep/shipping')->load((int)$id);


        if ($model->getId() || !$id) {
            Mage::register('mep_shipping', $model);
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if ($data) {
                $model->setData($data)->setId($id);
            } else {
                Mage::getSingleton('adminhtml/session')->setMepProfileData($model->getData());
            }

            Mage::register('mep_shipping_data', $model);

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
            $model = Mage::getModel('mep/shipping');

            $id = $this->getRequest()->getParam('id');
            $data['id'] = $id;
            if ($id) {
                $model->load($id);
            }

            Mage::getSingleton('adminhtml/session')->setFormData($data);

            try {
                $model->setData($data);
                $model->save();

                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('mep')->__('Error saving profil'));
                }


                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mep')->__('Shipping profil was successfully saved'));
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
                Mage::getModel('mep/shipping')->load($id)->delete();
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
        } else {
            try {
                foreach ($productIds as $productId) {
                    Mage::getModel('mep/shipping')->load($productId)->delete();
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
}

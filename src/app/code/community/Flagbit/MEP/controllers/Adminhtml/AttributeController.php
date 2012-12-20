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

    /**
     * indexAction
     *
     * @return void
     */
    public function popupAction()
    {

        $this->loadLayout('empty')->renderLayout();
        $html = $this->getLayout()->createBlock('mep/adminhtml_profil_popup')->setTemplate('mep/popup.phtml')->toHtml();
        $this->getResponse()->setBody($html);
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
            Mage::register('mep_profil', $model);
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if ($data) {
                $model->setData($data)->setId($id);
            } else {
                Mage::getSingleton('adminhtml/session')->setMepProfileData($model->getData());
            }

            Mage::register('mep_profil_data', $model);

            $this->_initAction();
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->renderLayout();

            Mage::getSingleton('adminhtml/session')->setMepProfileData(null);
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
            $data['id'] = $id;
            if ($id) {
                $model->load($id);
            }

            if (isset($data['rule'])) {

                $data = $this->_filterDates($data, array('from_date', 'to_date'));

                if (isset($data['rule']['conditions'])) {
                    //$model->setConditionsSerialized($data['rule']['conditions']);
                    $data['conditions_serialized'] = $data['rule']['conditions'];
                    unset($data['rule']);
                }
            }

            Mage::getSingleton('adminhtml/session')->setFormData($data);

            try {
                $model->setData($data);
                $model->save();

                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('mep')->__('Error saving profil'));
                }

                // Template Stuff
                if (isset($data['template'])) {
                    $result = Mage::helper('mep')->setTemplateProfil($model->getId(), $data['template']);
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
        } else {
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

    public function runClickAction()
    {
        try {
            /** @var $model Mage_ImportExport_Model_Export */
            $model = Mage::getModel('mep/export');
            $model->setData($this->getRequest()->getParams());
            $model->setEntity("catalog_product");
            $model->setFileFormat("twig");
            $model->setExportFilter(array());

            return $this->_prepareDownloadResponse(
                $model->getFileName(),
                $model->export(),
                $model->getContentType()
            );

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());

        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('No valid data sent'));
            if(Mage::getIsDeveloperMode()){
                throw $e;
            }
        }
    }
}

<?php
class Flagbit_MEP_Adminhtml_Shipping_AttributeController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Render grid action
     */
    public function indexAction()
    {
        $this->_forward('grid');
    }

    /**
     * Add attribute field mappings to profile
     */
    public function editAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('mep/shipping_attribute');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model = Mage::getModel('mep/shipping_attribute')->load($id);
            }
            unset($data['id']);
            $model->addData($data);

            try {
                $model->save();
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mep')->__($e->getMessage()));
            }
        }
    }

    /**
     * Create ui dialog to add attribute field mappings to profile
     */
    public function popupAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * massDeleteAction
     *
     * @return void
     */
    public function massDeleteAction()
    {
        $profileId = $this->getRequest()->getParam('id', Mage::helper('mep')->getCurrentProfileData(true));
        $mappingIds = $this->getRequest()->getParam('mapping_id');

        if (!is_array($mappingIds)) {
            $this->_getSession()->addError($this->__('Please select Mapping(s).'));
        } else {
            try {
                foreach ($mappingIds as $mappingId) {
                    Mage::getModel('mep/shipping_attribute')->load($mappingId)->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d Mapping(s) have been deleted.', count($mappingIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/shipping/edit', array('id' => $profileId, 'tab' => 'mapping'));
            }
        }
        $this->_redirect('*/shipping/edit', array('id' => $profileId, 'tab' => 'mapping'));
    }

    /**
     * delete a field mapping.
     */
    public function deleteAction()
    {
        if ($this->getRequest()->has('mapping_id')) {
            $id = $this->getRequest()->getParam('mapping_id');
            $mapping = Mage::getModel('mep/shipping_attribute')->load($id);
            if ($mapping) {
                $mapping->delete();
            }
        }

        $profile_id = $this->getRequest()->getParam('id', Mage::helper('mep')->getCurrentProfileData(true));
        $this->_redirect('*/shipping/edit', array('id' => $profile_id, 'tab' => 'mapping'));

    }


    /**
     * Grid for AJAX request
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('mep/adminhtml_shipping_view_mapping_grid')->toHtml());
    }
}

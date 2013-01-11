<?php

class Flagbit_MEP_Adminhtml_MepController extends Mage_Adminhtml_Controller_Action
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
     * Check access (in the ACL) for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/convert/export');
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_title($this->__('Export'))
            ->_addBreadcrumb($this->__('Export'), $this->__('Export'));

        $this->renderLayout();

    }

    public function getFilterAction()
    {
        $data = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest() && $data) {
            try {
                $this->loadLayout();

                /** @var $attrFilterBlock Mage_ImportExport_Block_Adminhtml_Export_Filter */
                $attrFilterBlock = $this->getLayout()->getBlock('mep.filter');
                /** @var $export Mage_ImportExport_Model_Export */
                $export = Mage::getModel('mep/export');

                $export->filterAttributeCollection(
                    $attrFilterBlock->prepareCollection(
                        $export->setData($data)->getEntityAttributeCollection()
                    )
                );
                return $this->renderLayout();
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('No valid data sent'));
        }
        $this->_redirect('*/*/index');
    }
}

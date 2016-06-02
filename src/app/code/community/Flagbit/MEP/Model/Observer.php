<?php
class Flagbit_MEP_Model_Observer extends Varien_Object
{
    /**
     * Export all enabled profiles
     */
    public function exportEnabledProfiles()
    {
        /** @var $profile Flagbit_MEP_Model_Profile */
        foreach ($this->getProfileCollection() as $profile) {
            $this->exportProfile($profile);
        }
    }

    /**
     * run specific profile via cron
     *
     * @param $schedule
     */
    public function runProfile($profileId)
    {
        $profile = Mage::getModel('mep/profile')->load($profileId);
        if($profile->getId()){
            $this->exportProfile($profile);
        }
    }


    /**
     * Append a custom block to the category.product.grid block.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function catalogCategoryPrepareSave(Varien_Event_Observer $observer)
    {
        $data = $observer->getRequest()->getParam('mep');
        $category = $observer->getCategory();
        $storeId = $observer->getRequest()->getParam('store');

        if(!empty($data) && $category->getId() && $storeId){

            foreach($data as $id => $value){
                $id = ltrim($id, 'mapping_');
                $model = Mage::getModel('mep/attribute_mapping')->load($id);
                $model->load($id);

                try {

                    $model->setOption(
                        array('value' => array(
                                $category->getId() => array(
                                                    $storeId => $value
                                                      )
                                        )
                            )
                    );
                    $model->save();
                }catch (Exception $e){
                    Mage::logException($e);
                }
            }
        }
    }

    /**
     * Append a custom Tab to the category page
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function adminhtmlCatalogCategoryTabs(Varien_Event_Observer $observer)
    {
        $tabs = $observer->getEvent()->getTabs();

        if($tabs->getCategory()->getStoreId() !== 0){
            $tabs->addTab(
                'mep',
                array(
                    'label'   => Mage::helper('catalog')->__('MEP Mappings'),
                    'content' => $tabs->getLayout()->createBlock('mep/adminhtml_category_mapping', '')->toHtml()
                )
            );
        }
    }

    /**
     * export Profile
     *
     * @param Flagbit_MEP_Model_Profile $profile
     */
    public function exportProfile(Flagbit_MEP_Model_Profile $profile, $catchErrors = true)
    {
        $exportFile = null;
        $newTempExportFile = null;
        try{
            /** @var $appEmulation Mage_Core_Model_App_Emulation */
            $appEmulation = Mage::getSingleton('core/app_emulation');
            //Start environment emulation of the specified store
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($profile->getStoreId(), Mage_Core_Model_App_Area::AREA_ADMINHTML);

            // Initialize frontend translations
            Mage::getSingleton('core/translate')->init('frontend', true);

            // destination File
            $exportFile = $this->_getExportPath($profile) . DS . $profile->getFilename();
            $newTempExportFile = $exportFile . '.new';
            if(file_exists($newTempExportFile)){
                unlink($newTempExportFile);
            }

            // disable flat Tables
            Mage::app()->getConfig()->setNode('catalog/frontend/flat_catalog_product',0,true);

            // add additional Logfile for the current Profile
            Mage::helper('mep/log')->addAdditionalLogfile('mep-'.$profile->getId().'.log');

            /* @var $export Flagbit_MEP_Model_Export */
            $export = Mage::getModel('mep/export');
            $export->setData('id', $profile->getId());
            $export->setEntity("catalog_product");
            $export->setFileFormat("twig");
            $export->setExportFilter(array());
            $export->setDestination($exportFile);
            $export->export();

            try{ // bypass MySQL server has gone away errors
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            }catch (Exception $e){}

        }catch (Exception $e){
            Mage::helper('mep/log')->err($e, $this);
            Mage::logException($e);

            if(!$catchErrors){
                throw $e;
            }
        }

        return $exportFile;
    }

    /**
     * Get all enabled export profiles.
     *
     * @return Flagbit_MEP_Model_Mysql4_Profile_Collection
     */
    public function getProfileCollection()
    {
        /* @var $profiles Flagbit_MEP_Model_Mysql4_Profile_Collection */
        $profiles = Mage::getModel('mep/profile')->getCollection();
        $profiles->addFieldToFilter('status', 1);
        return $profiles;
    }

    /**
     * Get the export path
     *
     * @param $profile Flagbit_MEP_Model_Profile
     * @return string
     */
    protected function _getExportPath($profile)
    {
        $exportDir = Mage::getConfig()->getOptions()->getBaseDir() . DS . $profile->getFilepath();

        if(Mage::getConfig()->getOptions()->createDirIfNotExists($exportDir) === FALSE){
            Mage::throwException('Export Directory is not writable ('.$exportDir.')');
        }

        return $exportDir;
    }
}

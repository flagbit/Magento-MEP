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
     * export Profile
     *
     * @param Flagbit_MEP_Model_Profile $profile
     */
    public function exportProfile(Flagbit_MEP_Model_Profile $profile)
    {
        $exportFile = null;
        try{
            /** @var $appEmulation Mage_Core_Model_App_Emulation */
            $appEmulation = Mage::getSingleton('core/app_emulation');
            //Start environment emulation of the specified store
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($profile->getStoreId());

            // disable flat Tables
            Mage::app()->getConfig()->setNode('catalog/frontend/flat_catalog_product',0,true);

            /* @var $export Mage_ImportExport_Model_Export */
            $export = Mage::getModel('mep/export');
            $export->setData('id', $profile->getId());
            $export->setEntity("catalog_product");
            $export->setFileFormat("twig");
            $export->setExportFilter(array());
            $exportFile = $this->_getExportPath($profile) . DS . $profile->getFilename();
            file_put_contents($exportFile, $export->export());

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }catch (Exception $e){
            Mage::logException($e);
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
     * @param $profile Flagbit_MEP_Model_Profil
     * @return string
     */
    protected function _getExportPath($profile)
    {
        $exportDir = Mage::getConfig()->getOptions()->getBaseDir() . DS . $profile->getFilepath();
        Mage::getConfig()->getOptions()->createDirIfNotExists($exportDir);
        return $exportDir;
    }
}

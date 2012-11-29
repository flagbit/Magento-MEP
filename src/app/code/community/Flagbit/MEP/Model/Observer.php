<?php
class Flagbit_MEP_Model_Observer extends Varien_Object
{
    /**
     * Export all enabled profiles
     */
    public function exportEnabledProfiles()
    {
        /** @var $profile Flagbit_MEP_Model_Profil */
        foreach ($this->getProfileCollection() as $profile) {
            /* @var $export Mage_ImportExport_Model_Export */
            $export = Mage::getModel('importexport/export');
            $export->setData('id',$profile->getId());
            $export->setEntity("catalog_product2");
            $export->setFileFormat("csv");
            $export->setExportFilter(array());
            $exportFile = Mage::getConfig()->getOptions()->getExportDir() . DS . $export->getFileName();
            file_put_contents($exportFile,$export->export());
        }
    }

    /**
     * Get all enabled export profiles.
     * @return Flagbit_MEP_Model_Mysql4_Profil_Collection
     */
    protected function getProfileCollection()
    {
        /* @var $profiles Flagbit_MEP_Model_Mysql4_Profil_Collection */
        $profiles = Mage::getModel('mep/profil')->getCollection();
        $profiles->addFieldToFilter('status', 1);
        return $profiles;
    }
}

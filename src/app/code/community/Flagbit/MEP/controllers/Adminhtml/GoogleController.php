<?php

class   Flagbit_MEP_Adminhtml_GoogleController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        if ($storeId) {
            Mage::register('category_store_id', $storeId);
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function loadcategoriesAction()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        if ($storeId)  {
            Mage::register('category_store_id', $storeId);
        }

        $categoriesTree = Mage::helper('mep/categories')->getCategoriesTree();
        $array = array();
        Mage::helper('mep/categories')->loadCategoryTreeForGoogle($categoriesTree, 1, $array);
        $this->getResponse()->clearHeaders()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody(json_encode($array));
    }

    public function loadlanguageAction()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        $strLang = null;

        if ($storeId)  {
            Mage::register('category_store_id', $storeId);
            $strLang = Mage::helper('mep/storelang')->getLanguageForStoreId($storeId);
        }

        $this->getResponse()->clearHeaders()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody(json_encode(['language' => $strLang]));
    }

    public function loadtaxonomiesAction()
    {
        $taxonomyId = $this->getRequest()->getParam('taxonomy_id');
        if ($taxonomyId)
        {
            $taxonomies = Mage::helper('mep/categories')->getArrayForTaxonomy($taxonomyId);
            $this->getResponse()->clearHeaders()->setHeader('Content-Type', 'application/json');
            $this->getResponse()->setBody(json_encode($taxonomies));
        }
    }

    public function saveAction()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        if (is_null($storeId))  {
            $storeId = Mage::app()->getStore()->getStoreId();
        }
        if ($data = $this->getRequest()->getPost())
        {

            if(isset($data['mep_store_language'])) {
                Mage::getModel('mep/googleStorelang')
                    ->load($storeId)
                    ->setData('store_id', $storeId)
                    ->setData('language', $data['mep_store_language'])
                    ->save();
            }

            $mappings = Mage::helper('mep/categories')->prepareMappingForSave($data['google-mapping']);
            try
            {
                foreach ($mappings as $mapping)
                {
                    $newMapping = Mage::getModel('mep/googleMapping')->loadByCategoryAndStore($mapping['category_id'], $storeId);
                    $newMapping->setData('category_id', $mapping['category_id']);
                    $newMapping->setData('google_mapping_ids', $mapping['google_mapping_ids']);
                    $newMapping->setData('store_id', $storeId);
                    $result = $newMapping->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mep')->__('Mapping was successfully saved'));
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            $this->_redirect('*/*/', array('store_id' => $storeId));
        }
    }

    public function importcategoriesAction()
    {
        $url = Mage::helper('mep/categories')->getGoogleCategoriesFileUrl();
        /** @var Flagbit_MEP_Model_GoogleMapping_Import $importModel */
        $importModel = Mage::getSingleton('mep/googleMapping_import');
        try
        {
            $importModel->runImportWithUrl($url);
        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->getResponse()->setHeader('Content-Type', 'application/json');
            $this->getResponse()->setBody(json_encode(array('error' => $e->getMessage())));
        }
    }

    public function importcategoriesmultistoreAction()
    {
        $urls = Mage::helper('mep/categories')->getGoogleCategoriesFileUrls();
        /** @var Flagbit_MEP_Model_GoogleMapping_Import $importModel */
        $importModel = Mage::getSingleton('mep/googleMapping_import');
        try
        {
            $importModel->runImportWithUrls($urls);
        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->getResponse()->setHeader('Content-Type', 'application/json');
            $this->getResponse()->setBody(json_encode(array('error' => $e->getMessage())));
        }
    }
}

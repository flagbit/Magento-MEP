<?php

class   Flagbit_MEP_Helper_Storelang extends Mage_Core_Helper_Abstract
{
    public function getLanguages() {
        $collection = Mage::getModel('mep/googleTaxonomies')
            ->getCollection()
            ->removeAllFieldsFromSelect()
            ->addFieldToSelect('locale');

        $collection->getSelect()->group('main_table.locale');

        return $collection;
    }

    public function getStoreLanguages() {
        $allLanguages = Mage::app()->getLocale()->getOptionLocales();

        $result = [];
        foreach($allLanguages as $lang) {
            $result[] = $lang['value'];
        }

        return $result;
    }

    public function getLanguagesForForm() {
        $result = [
            [
                'label' => '',
                'value' => '',
            ]
        ];
        foreach($this->getLanguages() as $lang) {
            $result[] = [
                'label' => $lang->getData('locale'),
                'value' => $lang->getData('locale'),
            ];
        }

        return $result;
    }

    public function getLanguageForStoreId($storeId) {
        $lang = Mage::getModel('mep/googleStorelang')->load($storeId);
        if($lang) {
            return $lang->getData('language');
        } else {
            return null;
        }
    }
}

<?php

class Flagbit_MEP_Block_Adminhtml_Google_View_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function  _prepareForm()
    {
        if (!Mage::helper('mep/categories')->googleCategoriesAreInitialized())
        {
            $form = new Varien_Data_Form(array());

            $formUpload = $form->addFieldset('file_selection',
                array(
                    'legend' => Mage::helper('mep')->__('Google Categories CSV')
                ));

            if (Mage::app()->isSingleStoreMode()) {
                $formUpload->addField('launch', 'button',
                    array(
                        'label' => Mage::helper('mep')->__('Google categories initialisation'),
                        'value' => Mage::helper('mep')->__('Start'),
                        'name' => 'launch',
                        'class' => 'form-button',
                        'onclick' => 'startGoogleCategoriesImport(\'' . Mage::helper('adminhtml')->getUrl('adminhtml/google/importcategories') . '\');',
                    ));
            } else {
                $formUpload->addField('launch', 'button',
                    array(
                        'label' => Mage::helper('mep')->__('Google categories initialisation'),
                        'value' => Mage::helper('mep')->__('Start'),
                        'name' => 'launch',
                        'class' => 'form-button',
                        'onclick' => 'startGoogleCategoriesImport('
                            . '\'' . Mage::helper('adminhtml')->getUrl('adminhtml/google/importcategoriesmultistore') . '\');',
                    ));

            }
        }
        else
        {
            $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save'),
                'method' => 'post',
            ));

            $storeSelection = $form->addFieldset('store_selection',
                array(
                    'legend' => Mage::helper('mep')->__('Select a store')
                ));

            if (!Mage::app()->isSingleStoreMode()) {

                $storeSelection->addField('store_selection_select', 'select',
                    array(
                        'label' => Mage::helper('mep')->__('Store'),
                        'class' => 'required-entry',
                        'required' => true,
                        'name' => 'store_id',
                        'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
                    )
                );

                $afterElementHtml = '<p class="nm"><small>' . 'Press save to reload taxonomy for the selected language!!' . '</small></p>';

                $storeSelection->addField('mep_store_language', 'select',
                    array(
                        'label' => Mage::helper('mep')->__('Language'),
                        'class' => 'required-entry',
                        'required' => true,
                        'name' => 'mep_store_language',
                        'values'    => Mage::helper('mep/storelang')->getLanguagesForForm(),
                        'after_element_html' => $afterElementHtml,
                    )
                );

                $storeId = Mage::registry('category_store_id');
                if($storeId) {
                    $form->setValues([
                        'store_selection_select' => $storeId,
                        'mep_store_language' => Mage::helper('mep/storelang')->getLanguageForStoreId($storeId),
                    ]);
                }

            } else {
                $storeSelection->addField('store_id', 'hidden', array(
                    'name' => 'store_id',
                    'value' => Mage::app()->getStore(true)->getId()
                ));

                $storeId = Mage::register('store_id');
                if($storeId) {
                    $form->setValues(['store_id' => $storeId]);
                }
            }

            $categories = $form->addFieldset('categories', array(
                'legend' => Mage::helper('mep')->__('Categories mapping'),
            ));

            $categories->setHtmlContent('
                <div id="categories_list"></div>
                <script type="text/javascript">
                    $(document).observe("dom:loaded", function() {
                        var googleMapping = new GoogleMapping();
                        googleMapping.options.requestUrl.loadcategories = \'' . Mage::helper('adminhtml')->getUrl('adminhtml/google/loadcategories') . '\';
                        googleMapping.options.requestUrl.loadtaxonomies = \'' . Mage::helper('adminhtml')->getUrl('adminhtml/google/loadtaxonomies') . '\';
                        googleMapping.options.requestUrl.loadlanguage = \'' . Mage::helper('adminhtml')->getUrl('adminhtml/google/loadlanguage') . '\';
                        googleMapping.load();
                    });
                </script>
            ');
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

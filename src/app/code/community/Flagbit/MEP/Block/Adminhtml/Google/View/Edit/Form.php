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

            $formUpload->addField('launch', 'button',
                array(
                    'label' => Mage::helper('mep')->__('Google categories initialisation'),
                    'value' => Mage::helper('mep')->__('Start'),
                    'name' => 'launch',
                    'class' => 'form-button',
                    'onclick' => 'startGoogleCategoriesImport(\'' . Mage::helper('adminhtml')->getUrl('/google/importcategories') . '\');',
                ));
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

            $storeSelection->addField('store_selection_select', 'select',
                array(
                    'label' => Mage::helper('mep')->__('Store'),
                    'class' => 'required-entry',
                    'required' => true,
                    'name' => 'store_selection_select',
                    'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
                ));

            $categories = $form->addFieldset('categories', array(
                'legend' => Mage::helper('mep')->__('Categories mapping'),
            ));

            $categories->setHtmlContent('
                <div id="categories_list"></div>
                <script type="text/javascript">
                    $(document).observe("dom:loaded", function() {
                        var googleMapping = new GoogleMapping();
                        googleMapping.options.requestUrl.loadcategories = \'' . Mage::helper('adminhtml')->getUrl('/google/loadcategories') . '\';
                        googleMapping.options.requestUrl.loadtaxonomies = \'' . Mage::helper('adminhtml')->getUrl('/google/loadtaxonomies') . '\';
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
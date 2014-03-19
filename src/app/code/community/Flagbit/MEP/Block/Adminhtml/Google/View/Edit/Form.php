<?php

class Flagbit_MEP_Block_Adminhtml_Google_View_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function  _prepareForm()
    {
        $form = new Varien_Data_Form();

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
            'after_element_html' => '
                <script type="text/javascript">
                    $(document).observe("dom:loaded", function() {
                        var googleMapping = new GoogleMapping();
                    });
                </script>
            ',
        ));

        $categories->setHtmlContent('
            <div id="categories_list"></div>
            <script type="text/javascript">
                $(document).observe("dom:loaded", function() {
                    var googleMapping = new GoogleMapping(\'' . Mage::helper('adminhtml')->getUrl('/google/loadcategories') . '\');
                    googleMapping.load();
                });
            </script>
        ');

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
<?php
/**
 * This file is part of the FIREGENTO project.
 *
 * FireGento_DynamicCategory is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This script is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * PHP version 5
 *
 * @category  FireGento
 * @package   FireGento_DynamicCategory
 * @author    FireGento Team <team@firegento.com>
 * @copyright 2012 FireGento Team (http://www.firegento.de). All rights served.
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version   1.0.0
 * @since     0.1.0
 */
/**
 * Condition block for category edit page
 *
 * @category  FireGento
 * @package   FireGento_DynamicCategory
 * @author    FireGento Team <team@firegento.com>
 * @copyright 2012 FireGento Team (http://www.firegento.de). All rights served.
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version   1.0.0
 * @since     0.1.0
 */
class Flagbit_MEP_Block_Adminhtml_Category_Dynamic
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Category Model
     *
     * @var Mage_Catalog_Model_Category Category
     */
    protected $_category = null;

    /**
     * Retrieve the current categoy
     *
     * @return Mage_Catalog_Model_Category Category
     */
    public function getCategory()
    {
        if ($this->_category == null) {
            $this->_category = Mage::registry('mep_profil');
        }
        return $this->_category;
    }

    /**
     * Creates the form for the condition based selection of product attributes.
     *
     * @return FireGento_DynamicCategory_Block_Adminhtml_Category_Dynamic Self.
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        $model = Mage::getSingleton('mep/rule');
        $data = array();
        if ($this->getCategory() != null) {
            $data['conditions'] = @unserialize($this->getCategory()->getConditionsSerialized());
        }
        $model->loadPost($data);

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('mep_');
        $form->setDataObject($this->getCategory());

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('mep/fieldset.phtml')
            ->setNewChildUrl(
                $this->getUrl('adminhtml/dynamic/newConditionHtml/form/mep_conditions_fieldset')
            );

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            array('legend' => $this->__('Export Rules'))
        )->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            array(
                'name' => 'conditions',
                'label' => $this->__('Conditions'),
                'title' => $this->__('Conditions'),
            )
        )->setRule($model)->setRenderer(Mage::getBlockSingleton('mep/conditions'));

        $specialRules = $form->addFieldset(
            'special_rules_fieldset',
            array('legend' => $this->__('Special filters'))
        );

        $specialRules->addType('apply', 'Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Apply');
        $specialRules->addField('apply_to', 'apply', array(
            'name'        => 'settings[apply_to][]',
            'label'       => Mage::helper('catalog')->__('Apply To'),
            'values'      => Mage_Catalog_Model_Product_Type::getOptions(),
            'mode_labels' => array(
                'all'     => Mage::helper('catalog')->__('All Product Types'),
                'custom'  => Mage::helper('catalog')->__('Selected Product Types')
            ),
            'required'    => true,
        ), 'frontend_class');
        $form->getElement('apply_to')->setValue($this->getApplyToValue($form));

        $specialRules->addField('is_in_stock', 'select', array(
            'name'  => 'settings[is_in_stock]',
            'label' => Mage::helper('catalog')->__('Stock Availability'),
            'values' =>  array_merge(Mage::getSingleton('cataloginventory/source_stock')->toOptionArray(), array('2' => '')),
            'value' => $this->getProfilSettingsValueForKey('is_in_stock')
        ));

        $specialRules->addType('qty', 'Flagbit_MEP_Helper_QtyFilter');
        $specialRules->addField('qty', 'qty', array(
            'name'  => 'settings[qty][threshold]',
            'dropdownName' => 'settings[qty][operator]',
            'dropdownStyle' => 'width: 150px',
            'dropdownValue' => $this->getQtyOperatorValue(),
            'label' => Mage::helper('catalog')->__('Qty'),
            'style' => 'width:50px',
            'required' => true
        ));
        $form->getElement('qty')->setValue($this->getQtyFilterValue($form));

        $profilData = Mage::helper('mep')->getCurrentProfileData();
        $settings = $profilData['settings'];
        if (!$settings) {
            $form->getElement('apply_to')->addClass('no-display ignore-validate');
            $form->getElement('qty')->addClass('no-display ignore-validate');
        }

        $this->setForm($form);

        return $this;
    }

    protected function  getProfilSettingsValueForKey($key) {
        $profilData = Mage::helper('mep')->getCurrentProfileData();
        $settings = $profilData['settings'];
        if (isset($settings[$key]) && ($value = $settings[$key]) !== false) {
            return $value;
        }
        return '';
    }

    protected function  getApplyToValue(&$form) {
        $profilData = Mage::helper('mep')->getCurrentProfileData();
        $settings = $profilData['settings'];
        if (isset($settings['apply_to']) && ($product_type = $settings['apply_to'])) {
            $product_type = is_array($product_type) ? $product_type : explode(',', $product_type);
            return $product_type;
        }
        else {
            $form->getElement('apply_to')->addClass('no-display ignore-validate');
        }
        return null;
    }

    protected function  getQtyFilterValue(&$form) {
        $profilData = Mage::helper('mep')->getCurrentProfileData();
        $settings = $profilData['settings'];
        if (isset($settings['qty']) && isset($settings['qty']['threshold']) && strlen($settings['qty']['threshold'])) {
            return $settings['qty']['threshold'];
        }
        else {
            $form->getElement('qty')->addClass('no-display ignore-validate');
        }
        return null;
    }

    protected function  getQtyOperatorValue() {
        $profilData = Mage::helper('mep')->getCurrentProfileData();
        $settings = $profilData['settings'];
        if (isset($settings['qty']) && isset($settings['qty']['operator']) && ($operator = $settings['qty']['operator'])) {
            return $operator;
        }
        return null;
    }
}

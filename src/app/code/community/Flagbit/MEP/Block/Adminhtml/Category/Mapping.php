<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml Catalog Category Attributes per Group Tab block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Flagbit_MEP_Block_Adminhtml_Category_Mapping extends Mage_Adminhtml_Block_Catalog_Form
{
    /**
     * Retrieve Category object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory()
    {
        return Mage::registry('current_category');
    }

    /**
     * Initialize tab
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    protected function getHintByType($type)
    {
        $types = array(
                        'single'    => Mage::helper('mep')->__('each single Category will be mapped'),
                        'complete'  => Mage::helper('mep')->__('one Category contains the full Path'),
                      );
        if(isset($types[$type])){
            return Mage::helper('mep')->__('Mapping Type').': '.$types[$type];
        }
        return '';
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Catalog_Category_Tab_Attributes
     */
    protected function _prepareForm() {

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('group_mep');
        $form->setDataObject($this->getCategory());

        $fieldset = $form->addFieldset('fieldset_group_mep', array(
            'legend'    => Mage::helper('mep')->__('MEP Mappings'),
            'class'     => 'fieldset-wide',
        ));

        /* @var $collection Flagbit_MEP_Model_Mysql4_Attribute_Mapping_Collection */
        $collection = Mage::getModel('mep/attribute_mapping')->getCollection()->addFieldToFilter('source_attribute_code', 'category');

        foreach($collection as $mapping){
            $fieldset->addField('mapping_'.$mapping->getId(), 'text', array(
                'label'   => $mapping->getName(),
                'name'    => 'mapping_'.$mapping->getId(),
                'note' => $this->getHintByType($mapping->getCategoryType()).', '.Mage::helper('mep')->__('Attribute Code: %s', $mapping->getAttributeCode())
            ));
        }

        $form->addValues($this->_getValuesByMappingCollection($collection, $this->getCategory()->getStoreId()));
        $form->setFieldNameSuffix('mep');
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * get Values
     *
     * @param Flagbit_MEP_Model_Mysql4_Attribute_Mapping_Collection $collection
     * @param $storeId
     */
    protected function _getValuesByMappingCollection($collection, $storeId)
    {
        $values = array();
        /* @var $valuesCollection Flagbit_MEP_Model_Mysql4_Attribute_Mapping_Option_Collection */
        $valuesCollection = Mage::getResourceModel('mep/attribute_mapping_option_collection')
            ->addFieldToFilter('parent_id', array('in' => $collection->getAllIds()))
            ->addFieldToFilter('option_id', $this->getCategory()->getId())
            ->setStoreFilter($storeId, false)
            ->load();

        foreach($valuesCollection as $value){
            $mapping = $collection->getItemById($value->getParentId());
            $values['mapping_'.$mapping->getId()] = $value->getValue();
        }
        return $values;
    }
}




































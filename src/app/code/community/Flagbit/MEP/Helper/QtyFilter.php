<?php

class Flagbit_MEP_Helper_QtyFilter extends Varien_Data_Form_Element_Text
{
    public function getElementHtml()
    {
        $elementAttributeHtml = '';

        if ($this->getReadonly()) {
            $elementAttributeHtml = $elementAttributeHtml . ' readonly="readonly"';
        }

        if ($this->getDisabled()) {
            $elementAttributeHtml = $elementAttributeHtml . ' disabled="disabled"';
        }

        $operator = array(
            ''    => '',
            '=='  => Mage::helper('rule')->__('is'),
            '!='  => Mage::helper('rule')->__('is not'),
            '>='  => Mage::helper('rule')->__('equals or greater than'),
            '<='  => Mage::helper('rule')->__('equals or less than'),
            '>'   => Mage::helper('rule')->__('greater than'),
            '<'   => Mage::helper('rule')->__('less than'),
        );

        $html = '<select name="' . $this->getData('dropdownName') . '" onchange="toggleQtyFilterVisibility(this)"' . $elementAttributeHtml . ' style="' . $this->getData('dropdownStyle') . '">';
        foreach ($operator as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($this->getData('dropdownValue')==$key ? 'selected' : '') . '>' . $value . '</option>';
        }
        $html .= '</select>&nbsp;&nbsp;';
        $html .= parent::getElementHtml();
        return $html;
    }

    /**
     * Dublicate interface of Varien_Data_Form_Element_Abstract::setReadonly
     *
     * @param bool $readonly
     * @param bool $useDisabled
     * @return Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Apply
     */
    public function setReadonly($readonly, $useDisabled = false)
    {
        $this->setData('readonly', $readonly);
        $this->setData('disabled', $useDisabled);
        return $this;
    }

    public function  getOperatorForCollectionFilter($operator) {
        $operators = array(
            '==' => 'eq',
            '!=' => 'neq',
            '>=' => 'gteq',
            '<=' => 'lteq',
            '>' => 'gt',
            '<' => 'lt'
        );
        return $operators[$operator];
    }

    public function  getOperatorForSqlFilter($operator) {
        $operators = array(
            '==' => '=',
            '!=' => '!=',
            '>=' => '>=',
            '<=' => '<=',
            '>' => '>',
            '<' => '<'
        );
        return $operators[$operator];
    }

}

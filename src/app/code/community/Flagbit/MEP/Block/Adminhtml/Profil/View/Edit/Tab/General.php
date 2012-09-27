<?php
class Flagbit_MEP_Block_Adminhtml_Profil_View_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * _prepareForm
     * 
     * Prepares the edit form
     * 
     * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
     * 
     * @return Flagbit_MEP_Block_Adminhtml_View_Edit_Tab_General Self.
     */
	protected function _prepareForm()
	{
        if (Mage::getSingleton('adminhtml/session')->getProselmaBillingsData()) {
			$data = Mage::getSingleton('adminhtml/session')->getProselmaBillingsData();
			Mage::getSingleton('adminhtml/session')->setProselmaBillingsData(null);
		} elseif (Mage::registry('proselma_billings_data')) {
			$data = Mage::registry('proselma_billings_data')->getData();
		}
		else {
			$data = array();
		}

		$form = new Varien_Data_Form();
		$this->setForm($form);

		// PROMOTION FIELDSET
		$fieldset = $form->addFieldset(
			'billings_promotion_form',
		    array(
				'legend' => Mage::helper('billings')->__('Promotion')
		    )
		);
        $fieldset->addField(
        	'vendor_id',
        	'label',
            array(
				'label'    => Mage::helper('billings')->__('Vendor ID'),
				'name'     => 'vendor_id',
		    )
        );
        $fieldset->addField(
        	'promotion_id',
        	'label',
            array(
				'label'    => Mage::helper('billings')->__('Promotion ID'),
				'name'     => 'promotion_id',
		    )
		);
		$fieldset->addField(
			'name',
			'label',
		    array(
				'label'    => Mage::helper('billings')->__('Promotion Name'),
				'name'     => 'name',
		    )
        );
        $fieldset->addField(
        	'sku',
        	'label',
            array(
				'label'    => Mage::helper('billings')->__('SKU'),
                'name'     => 'sku',
		    )
		);
        $fieldset->addField(
        	'promotion_price',
        	'text',
            array(
				'label'    => Mage::helper('billings')->__('Promotion Price'),
				'class'    => 'required-entry',
				'required' => true,
				'name'     => 'promotion_price',
		    )
		);
        $fieldset->addField(
        	'cost_rate',
        	'text',
            array(
				'label'    => Mage::helper('billings')->__('Cost Rate (in %)'),
				'class'    => 'required-entry',
				'required' => true,
				'name'     => 'cost_rate',
		    )
		);
		
		// ORDER FIELDSET
        $fieldset = $form->addFieldset(
			'billings_order_form',
		    array(
				'legend' => Mage::helper('billings')->__('Order')
		    )
		);
        $fieldset->addField(
        	'order_id',
        	'label',
            array(
				'label'    => Mage::helper('billings')->__('Order ID'),
				'name'     => 'order_id',
		    )
        );
        $fieldset->addField(
        	'increment_id',
        	'label',
            array(
				'label'    => Mage::helper('billings')->__('Increment ID'),
				'name'     => 'increment_id',
		    )
        );
        $fieldset->addField(
        	'customer_id',
        	'label',
            array(
				'label'    => Mage::helper('billings')->__('Customer'),
				'name'     => 'customer_id',
		    )
        );
        $fieldset->addField(
        	'date',
        	'label',
            array(
				'label'    => Mage::helper('billings')->__('Date'),
				'name'     => 'date',
		    )
		);
        $fieldset->addField(
        	'qty',
        	'text',
            array(
				'label'    => Mage::helper('billings')->__('Qty'),
				'class'    => 'required-entry',
				'required' => true,
				'name'     => 'qty',
		    )
        );
        $fieldset->addField(
        	'total',
        	'label',
            array(
				'label'    => Mage::helper('billings')->__('Total'),
				'class'    => 'required-entry',
				'name'     => 'total',
		    )
		);
        $fieldset->addField(
        	'revenue',
        	'label',
            array(
				'label'    => Mage::helper('billings')->__('Revenue for Proselma'),
				'readonly' => true,
                'disabled' => true,
				'name'     => 'revenue'
		    )
		);
		$form->setValues($data);
		return parent::_prepareForm();
	}
}
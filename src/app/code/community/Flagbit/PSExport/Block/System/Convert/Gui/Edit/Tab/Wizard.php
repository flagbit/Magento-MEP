<?php

class Flagbit_PSExport_Block_System_Convert_Gui_Edit_Tab_Wizard extends Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tab_Wizard {
	

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('flagbit/psexport/wizard.phtml');
    }	

    public function getAttributes($entityType)
    {

        if (!isset($this->_attributes[$entityType])) {
            switch ($entityType) {
                case 'product':
                	
                    $attributes = Mage::getSingleton('catalog/convert_parser_product')
                        ->getExternalAttributes();    
                    break;

                case 'customer':
                    $attributes = Mage::getSingleton('customer/convert_parser_customer')
                        ->getExternalAttributes();
                    break;
            }

            array_splice($attributes, 0, 0, array(''=>$this->__('Choose an attribute')));
            $this->_attributes[$entityType] = $attributes;
        }
        return $this->_attributes[$entityType];
    }
    
    public function isMultiple($key){
    	return strstr($this->getData($key), ',') ? true : false;
    }

    public function getSelected($key, $value)
    {  	
    	if(!$value){
    		return '';
    	}
    	if(strstr($this->getData($key), ',')){ 
    		$values = explode(',', $this->getData($key));
    		return in_array($value, $values) ? 'selected="selected"' : '';
    	}
    	
        return $this->getData($key)==$value ? 'selected="selected"' : '';
    }   
	
}

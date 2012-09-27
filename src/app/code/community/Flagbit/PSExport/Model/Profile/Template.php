<?php

class Flagbit_PSExport_Model_Profile_Template {
	
	protected $_templates = array();
	
    /**
     * Constructor
     *
     */
    public function __construct()
    {
    	$this->_templates = Mage::getStoreConfig('flagbit_psexport/templates');   	
    }

    public function load($templateName){
    	
    	$template = null;
    	if(isset($this->_templates[$templateName])){  		
    		$template = $this->_templates[$templateName];
    		
    		$template['name'] = $this->_getUniqueProfileName($template['name']);
    		
    		if(isset($template['gui_data']['map']['product'])){
    			
    			$newMapping = array();
    			foreach($template['gui_data']['map']['product'] as $field){
    				
    				$newMapping['db'][] = isset($field['db']) ? $field['db'] : '';
    				$newMapping['file'][] = isset($field['file']) ? $field['file'] : '';
    				$newMapping['format'][] = isset($field['format']) ? $field['format'] : '';
    				
    			}
    			$template['gui_data']['map']['product'] = $newMapping;
    		}
    	}
    	
    	return $template;
    }
    
    protected function _getUniqueProfileName($name, $id = null){
    	
    	$profileResource = Mage::getModel('dataflow/profile')->getResource();
    	
    	if(!$profileResource->isProfileExists($name)){
    		return $name;
    	}
    	
    	for($i=1; $profileResource->isProfileExists($name.' '.$i, $id);$i++){}
    	return $name.' '.$i;
    }
	
    public function toOptionArray(){
    	
    	$options = array();
    	foreach($this->_templates as $key => $value){
    		$options[$key] = $this->__($value['name']);
    	}
    	return $options;
    }
    
    protected function __(){
    	$args = func_get_args();
    	return call_user_func_array(array(Mage::helper('flagbit_psexport'), '__'), $args);
    	
    }
    
}
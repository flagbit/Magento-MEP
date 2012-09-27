<?php

class Flagbit_PSExport_Adminhtml_DynamictextController extends Mage_Adminhtml_Controller_Action
{

	
    /**
     * Wisywyg widget plugin main page
     */
    public function indexAction()
    {
    	$data = array();
    	$attributes = Mage::getModel('catalog/convert_parser_product')->getExternalAttributes();
    	
    	$data[0]['label'] = $this->__('System Attributes');
    	   	
    	foreach($attributes as $key => $attribute){
    		if(is_array($attribute)){
    			
    			$subData = array();
    			foreach($attribute as $subKey => $subAttribute){
    				$subData[] = array(
		    				'value' => '{{get key="'.$subKey.'"}}',
		    				'label' => $subAttribute
    					);
    			}

    			$data[] = array(
    					'label' => $key,
    					'value' => $subData
    				);
    			
    			continue;
    		}
    		$data[0]['value'][] = array(
    				'value' => '{{get key="'.$key.'"}}',
    				'label' => $attribute
    			);
    	}
    	
    	$this->getResponse()->setBody(Zend_Json::encode($data));
    	return;
    }

}

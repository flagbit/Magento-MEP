<?php

class Flagbit_PSExport_Model_Convert_Mapper_Column extends Mage_Dataflow_Model_Convert_Mapper_Column
{


    public function map()
    {    	
        $batchModel  = $this->getBatchModel();
        $batchExport = $this->getBatchExportModel();

        $batchExportIds = $batchExport
            ->setBatchId($this->getBatchModel()->getId())
            ->getIdCollection();

        $onlySpecified = (bool)$this->getVar('_only_specified') === true;

        if (!$onlySpecified) {
            foreach ($batchExportIds as $batchExportId) {
                $batchExport->load($batchExportId);
                $batchModel->parseFieldList($batchExport->getBatchData());
            }

            return $this;
        }

        if ($this->getVar('map') && is_array($this->getVar('map'))) {
            $attributesToSelect = $this->getVar('map');
        }
        else {
            $attributesToSelect = array();
        }

        if ($this->getVar('map_format') && is_array($this->getVar('map_format'))) {
            $attributesToFormat = $this->getVar('map_format');
        }
        else {
            $attributesToFormat = array();
        }        
        
        if (!$attributesToSelect) {
            $this->getBatchExportModel()
                ->setBatchId($this->getBatchModel()->getId())
                ->deleteCollection();

            throw new Exception(Mage::helper('dataflow')->__('Error in field mapping: field list for mapping is not defined.'));
        }

        foreach ($batchExportIds as $batchExportId) {
            $batchExport = $this->getBatchExportModel()->load($batchExportId);
            $row = $batchExport->getBatchData();

            $newRow = array();
            foreach ($attributesToSelect as $field => $mapField) {
            	$orgField = $field;
            	
            	if(strstr($field, ',')){
            		$fields = explode(',', $field);
            		foreach($fields as $subField){
            			list($subField) = explode('::', $subField);
            			$newRow[$mapField][] = isset($row[$subField]) ? $row[$subField] : null;
            		}
            	}else{           	
	            	if(strstr($field, '::')){
	            		list($field) = explode('::', $field);
	            	}
	            	$newRow[$mapField] = isset($row[$field]) ? $row[$field] : null;
            	}
                
                if(!empty($attributesToFormat[$orgField])){
                	if(is_array($newRow[$mapField])){
                		$newRow[$mapField] = vsprintf($attributesToFormat[$orgField], $newRow[$mapField]);
                	}else{
                		$newRow[$mapField] = sprintf($attributesToFormat[$orgField], $newRow[$mapField]);
                	}
                }elseif(is_array($newRow[$mapField])){
                	$newRow[$mapField] = implode(' ', $newRow[$mapField]);
                }
                
                
                $newRow[$mapField] = str_replace(array("\n", "\r"), '', $newRow[$mapField]);
            }

            $batchExport->setBatchData($newRow)
                ->setStatus(2)
                ->save();
            $this->getBatchModel()->parseFieldList($batchExport->getBatchData());
        }

        return $this;
    }
    
}

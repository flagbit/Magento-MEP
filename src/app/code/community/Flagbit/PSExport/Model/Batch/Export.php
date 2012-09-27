<?php

class Flagbit_PSExport_Model_Batch_Export extends Mage_Dataflow_Model_Batch_Export
{
    public function setBatchData($data)
    {
		(Mage::registry('is_ascii'))?$encoding='ASCII//TRANSLIT':$encoding='utf-8//IGNORE';
    		foreach ($data as $key => &$value)
            {
            	$value = iconv('utf-8', $encoding, $value);
            }
        $this->setData('batch_data', serialize($data));
       	Mage::unregister('is_ascii');
        return $this;
    }
}
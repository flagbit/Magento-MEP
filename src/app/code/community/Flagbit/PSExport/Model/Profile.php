<?php

class Flagbit_PSExport_Model_Profile extends Mage_Dataflow_Model_Profile {
	
	protected $_history = null;
	
    /**
     * Create duplicate
     *
     * @return Flagbit_PSExport_Model_Profile
     */
    public function duplicate()
    {

        $newProfile = Mage::getModel('dataflow/profile')->setData($this->getData())
            ->setIsDuplicate(true)
            ->setOriginalId($this->getId())
            ->setCreatedAt(null)
            ->setUpdatedAt(null)
            ->setId(null);


        $newProfile->save();

        return $newProfile;
    }	

    protected function _beforeSave()
    {
    	$this->setName($this->_getUniqueProfileName($this->getName(), $this->getId()));
        parent::_beforeSave();
    }    
    
    
    protected function _getUniqueProfileName($name, $id = null){

    	$profileResource = Mage::getModel('dataflow/profile')->getResource();
    	
    	if(!$profileResource->isProfileExists($name, $id)){
    		return $name;
    	}
    	
    	for($i=1; $profileResource->isProfileExists($name.' '.$i, $id);$i++){}
    	return $name.' '.$i;
    }    
    
    protected function _afterSave()
    {
        if (is_string($this->getGuiData())) {
            $this->setGuiData(unserialize($this->getGuiData()));
        }

        Mage::getModel('dataflow/profile_history')
            ->setProfileId($this->getId())
            ->setActionCode($this->getOrigData('profile_id') ? 'update' : 'create')
            ->save();

        if (isset($_FILES['file_1']['tmp_name']) || isset($_FILES['file_2']['tmp_name']) || isset($_FILES['file_3']['tmp_name'])) {
            for ($index = 0; $index < 3; $index++) {
                if ($file = $_FILES['file_'.($index+1)]['tmp_name']) {
                    $uploader = new Varien_File_Uploader('file_'.($index+1));
                    $uploader->setAllowedExtensions(array('csv','xml'));
                    $path = Mage::app()->getConfig()->getTempVarDir().'/import/';
                    $uploader->save($path);
                    if ($uploadFile = $uploader->getUploadedFileName()) {
                        $newFilename = 'import-'.date('YmdHis').'-'.($index+1).'_'.$uploadFile;
                        rename($path.$uploadFile, $path.$newFilename);
                    }
                }
            }
        }
        parent::_afterSave();
    }
    
    public function getHistory(){
    	
    	if(!($this->_history instanceof Mage_Dataflow_Model_Profile_History)){
    		$this->_history = Mage::getModel('dataflow/profile_history');
    	}
    	return $this->_history;
    }
    

    /**
     * Run profile
     *
     * @return Mage_Dataflow_Model_Profile
     */
    public function run()
    {
        /**
         * Save history
         */
        $this->getHistory('dataflow/profile_history')
            ->setProfileId($this->getId())
            ->setActionCode('run')
            ->save();

        /**
         * Prepare xml convert profile actions data
         */
        $xml = '<convert version="1.0"><profile name="default">'.$this->getActionsXml().'</profile></convert>';
        $profile = Mage::getModel('core/convert')
            ->importXml($xml)
            ->getProfile('default');
        /* @var $profile Mage_Dataflow_Model_Convert_Profile */

        try {   	
        	setlocale(LC_ALL, Mage::getStoreConfig('general/locale/code', $this->getStoreId()).'.utf8');

            $batch = Mage::getSingleton('dataflow/batch')
                ->setProfileId($this->getId())
                ->setStoreId($this->getStoreId())
                ->save();
            $this->setBatchId($batch->getId());

            $profile->setDataflowProfile($this->getData());
            $profile->run();
            
        }
        catch (Exception $e) {
            echo $e;
        }

        $this->setExceptions($profile->getExceptions());
        return $this;
    }
	
	
    public function _parseGuiData()
    {
        $nl = "\r\n";
        $import = $this->getDirection()==='import';
        $p = $this->getGuiData();

        if ($this->getDataTransfer()==='interactive') {
//            $p['file']['type'] = 'file';
//            $p['file']['filename'] = $p['interactive']['filename'];
//            $p['file']['path'] = 'var/export';

            $interactiveXml = '<action type="dataflow/convert_adapter_http" method="'.($import?'load':'save').'">'.$nl;
            #$interactiveXml .= '    <var name="filename"><![CDATA['.$p['interactive']['filename'].']]></var>'.$nl;
            $interactiveXml .= '</action>';

            $fileXml = '';
        } else {

            $interactiveXml = '';

            $fileXml = '<action type="dataflow/convert_adapter_io" method="'.($import?'load':'save').'">'.$nl;
            $fileXml .= '    <var name="type">'.$p['file']['type'].'</var>'.$nl;
            $fileXml .= '    <var name="path">'.$p['file']['path'].'</var>'.$nl;
            $fileXml .= '    <var name="filename"><![CDATA['.$p['file']['filename'].']]></var>'.$nl;
            if ($p['file']['type']==='ftp') {
                $hostArr = explode(':', $p['file']['host']);
                $fileXml .= '    <var name="host"><![CDATA['.$hostArr[0].']]></var>'.$nl;
                if (isset($hostArr[1])) {
                    $fileXml .= '    <var name="port"><![CDATA['.$hostArr[1].']]></var>'.$nl;
                }
                if (!empty($p['file']['passive'])) {
                    $fileXml .= '    <var name="passive">true</var>'.$nl;
                }
                if (!empty($p['file']['user'])) {
                    $fileXml .= '    <var name="user"><![CDATA['.$p['file']['user'].']]></var>'.$nl;
                }
                if (!empty($p['file']['password'])) {
                    $fileXml .= '    <var name="password"><![CDATA['.$p['file']['password'].']]></var>'.$nl;
                }
            }
            if ($import) {
                $fileXml .= '    <var name="format"><![CDATA['.$p['parse']['type'].']]></var>'.$nl;
            }
            $fileXml .= '</action>'.$nl.$nl;
        }

        switch ($p['parse']['type']) {
            case 'excel_xml':
                $parseFileXml = '<action type="dataflow/convert_parser_xml_excel" method="'.($import?'parse':'unparse').'">'.$nl;
                $parseFileXml .= '    <var name="single_sheet"><![CDATA['.($p['parse']['single_sheet']!==''?$p['parse']['single_sheet']:'').']]></var>'.$nl;
                break;

            case 'csv':
                $parseFileXml = '<action type="dataflow/convert_parser_csv" method="'.($import?'parse':'unparse').'">'.$nl;
                $parseFileXml .= '    <var name="delimiter"><![CDATA['.$p['parse']['delimiter'].']]></var>'.$nl;
                $parseFileXml .= '    <var name="enclose"><![CDATA['.$p['parse']['enclose'].']]></var>'.$nl;
                break;
        }
        $parseFileXml .= '    <var name="fieldnames">'.$p['parse']['fieldnames'].'</var>'.$nl;
        $parseFileXmlInter = $parseFileXml;
        $parseFileXml .= '</action>'.$nl.$nl;

        $mapXml = '';

        if (isset($p['map']) && is_array($p['map'])) {
            foreach ($p['map'] as $side=>$fields) {
                if (!is_array($fields)) {
                    continue;
                }
                foreach ($fields['db'] as $i=>$k) {
                    if ($k=='' || $k=='0') {
                        unset($p['map'][$side]['db'][$i]);
                        unset($p['map'][$side]['file'][$i]);
                    }
                }
            }
        }
        $mapXml .= '<action type="dataflow/convert_mapper_column" method="map">'.$nl;
        $map = $p['map'][$this->getEntityType()];

        if (sizeof($map['db'])>0) {

            $from = $map[$import?'file':'db'];
            $to = $map[$import?'db':'file'];
            $mapXml .= '    <var name="map">'.$nl;
            $parseFileXmlInter .= '    <var name="map">'.$nl;
            foreach ($from as $i=>$f) {
            	$f = preg_replace('/([a-zA-Z0-9\_\-]+)\:/', '', $f);
                $mapXml .= '        <map name="'.$f.'" format="'.(isset($map['format'][$i])?$map['format'][$i]:'').'"><![CDATA['.$to[$i].']]></map>'.$nl;
                $parseFileXmlInter .= '        <map name="'.$f.'" format="'.(isset($map['format'][$i])?$map['format'][$i]:'').'"><![CDATA['.$to[$i].']]></map>'.$nl;
            }
            $mapXml .= '    </var>'.$nl;
            $parseFileXmlInter .= '    </var>'.$nl;
        }
        if ($p['map']['only_specified']) {
            $mapXml .= '    <var name="_only_specified">'.$p['map']['only_specified'].'</var>'.$nl;
            //$mapXml .= '    <var name="map">'.$nl;
            $parseFileXmlInter .= '    <var name="_only_specified">'.$p['map']['only_specified'].'</var>'.$nl;
        }
        $mapXml .= '</action>'.$nl.$nl;

        $parsers = array(
            'product'=>'catalog/convert_parser_product',
            'customer'=>'customer/convert_parser_customer',
        );

        if ($import) {
//            if ($this->getDataTransfer()==='interactive') {
                $parseFileXmlInter .= '    <var name="store"><![CDATA['.$this->getStoreId().']]></var>'.$nl;
//            } else {
//                $parseDataXml = '<action type="'.$parsers[$this->getEntityType()].'" method="parse">'.$nl;
//                $parseDataXml = '    <var name="store"><![CDATA['.$this->getStoreId().']]></var>'.$nl;
//                $parseDataXml .= '</action>'.$nl.$nl;
//            }
//            $parseDataXml = '<action type="'.$parsers[$this->getEntityType()].'" method="parse">'.$nl;
//            $parseDataXml .= '    <var name="store"><![CDATA['.$this->getStoreId().']]></var>'.$nl;
//            $parseDataXml .= '</action>'.$nl.$nl;
        } else {
            $parseDataXml = '<action type="'.$parsers[$this->getEntityType()].'" method="unparse">'.$nl;
            $parseDataXml .= '    <var name="store"><![CDATA['.$this->getStoreId().']]></var>'.$nl;
            if (isset($p['export']['add_url_field'])) {
                $parseDataXml .= '    <var name="url_field"><![CDATA['.$p['export']['add_url_field'].']]></var>'.$nl;
            }
            if (isset($p['export']['tracking_param'])) {
                $parseDataXml .= '    <var name="tracking_param"><![CDATA['.$p['export']['tracking_param'].']]></var>'.$nl;
            }            
            $parseDataXml .= '</action>'.$nl.$nl;
        }

        $adapters = array(
            'product'=>'catalog/convert_adapter_product',
            'customer'=>'customer/convert_adapter_customer',
        );

        if ($import) {
            $entityXml = '<action type="'.$adapters[$this->getEntityType()].'" method="save">'.$nl;
            $entityXml .= '    <var name="store"><![CDATA['.$this->getStoreId().']]></var>'.$nl;
            $entityXml .= '</action>'.$nl.$nl;
        } else {
            $entityXml = '<action type="'.$adapters[$this->getEntityType()].'" method="load">'.$nl;
            $entityXml .= '    <var name="store"><![CDATA['.$this->getStoreId().']]></var>'.$nl;
            if(isset($p[$this->getEntityType()]['filter'])){
	            foreach ($p[$this->getEntityType()]['filter'] as $f=>$v) {
	
	                if (empty($v)) {
	                    continue;
	                }
	                if (is_scalar($v)) {
	                    $entityXml .= '    <var name="filter/'.$f.'"><![CDATA['.$v.']]></var>'.$nl;
	                    $parseFileXmlInter .= '    <var name="filter/'.$f.'"><![CDATA['.$v.']]></var>'.$nl;
	                } elseif (is_array($v)) {
	                    foreach ($v as $a=>$b) {
	
	                        if (strlen($b) == 0) {
	                            continue;
	                        }
	                        $entityXml .= '    <var name="filter/'.$f.'/'.$a.'"><![CDATA['.$b.']]></var>'.$nl;
	                        $parseFileXmlInter .= '    <var name="filter/'.$f.'/'.$a.'"><![CDATA['.$b.']]></var>'.$nl;
	                    }
	                }
	            }
            }
            $entityXml .= '</action>'.$nl.$nl;
        }

        // Need to rewrite the whole xml action format
        if ($import) {
            $numberOfRecords = isset($p['import']['number_of_records']) ? $p['import']['number_of_records'] : 1;
            $decimalSeparator = isset($p['import']['decimal_separator']) ? $p['import']['decimal_separator'] : '.';
            $parseFileXmlInter .= '    <var name="number_of_records">'
                . $numberOfRecords . '</var>' . $nl;
            $parseFileXmlInter .= '    <var name="decimal_separator"><![CDATA['
                . $decimalSeparator . ']]></var>' . $nl;
            if ($this->getDataTransfer()==='interactive') {
                $xml = $parseFileXmlInter;
                $xml .= '    <var name="adapter">'.$adapters[$this->getEntityType()].'</var>'.$nl;
                $xml .= '    <var name="method">parse</var>'.$nl;
                $xml .= '</action>';
            } else {
                $xml = $fileXml;
                $xml .= $parseFileXmlInter;
                $xml .= '    <var name="adapter">'.$adapters[$this->getEntityType()].'</var>'.$nl;
                $xml .= '    <var name="method">parse</var>'.$nl;
                $xml .= '</action>';
            }
            //$xml = $interactiveXml.$fileXml.$parseFileXml.$mapXml.$parseDataXml.$entityXml;

        } else {
            $xml = $entityXml.$parseDataXml.$mapXml.$parseFileXml.$fileXml.$interactiveXml;
        }

        $this->setGuiData($p);
        $this->setActionsXml($xml);
/*echo "<pre>".print_r($p,1)."</pre>";
echo "<xmp>".$xml."</xmp>";
die;*/
        return $this;
    }	
	
}
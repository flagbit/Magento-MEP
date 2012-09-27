<?php

class Flagbit_PSExport_Model_Convert extends Mage_Core_Model_Convert
{
	
    public function importProfileXml($name)
    {
        if (!$this->_xml) {
            return $this;
        }
        $nodes = $this->_xml->xpath("//profile[@name='".$name."']");
        if (!$nodes) {
            return $this;
        }
        $profileNode = $nodes[0];

        $profile = $this->addProfile($name);
        $profile->setContainers($this->getContainers());
        foreach ($profileNode->action as $actionNode) {
            $action = $profile->addAction();
            foreach ($actionNode->attributes() as $key=>$value) {
                $action->setParam($key, (string)$value);
            }

            if ($actionNode['use']) {
                $container = $profile->getContainer((string)$actionNode['use']);
            } else {
                $action->setParam('class', $this->getClassNameByType((string)$actionNode['type']));
                $container = $action->getContainer();
            }
            $action->setContainer($container);
            if ($action->getParam('name')) {
                $this->addContainer($action->getParam('name'), $container);
            }
            foreach ($actionNode->var as $varNode) {
                if ($varNode['name'] == 'map') {
                	$mapData = array();
                	$formatData = array();
                    foreach ($varNode->map as $mapNode) {
                    	$_name = (string)$mapNode['name'];
                    	
                    	if(isset($mapData[$_name])){
                    		$_name = $this->_createUniqueArrayKey($_name, $mapData);
                    	}
                    	
                        $mapData[$_name] = (string)$mapNode;
                        $formatData[$_name] = (isset($mapNode['format']) ? (string)$mapNode['format'] : null);
                    }
                    $container->setVar((string)$varNode['name'], $mapData);
                    $container->setVar((string)$varNode['name'].'_format', $formatData);
                }
                else if($varNode['name'] == 'encoding') {
               		((string)$varNode==="true")?Mage::register('is_ascii', true):Mage::register('is_ascii', false);
                }
                else {
                    $container->setVar((string)$varNode['name'], (string)$varNode);
                }
            }
        }

        return $this;
    }

    protected function _createUniqueArrayKey($key, $array){
    	
    	for($i=1;isset($array[$key.'::'.$i]);$i++){}
    	return $key.'::'.$i;
    }

}

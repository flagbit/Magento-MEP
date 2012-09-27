<?php 

class Flagbit_PSExport_Model_Schedule {
	
	
    public function runMinutely($observer){
		$this->_runProfiles('minutely');
	}
    
	public function runDaily($observer){
		$this->_runProfiles('daily');
	}
	
	public function runWeekly($observer){
		$this->_runProfiles('weekly');
	}

	public function runMonthly($observer){
		$this->_runProfiles('monthly');
	}	
	
	protected function _getDesign(){
		return Mage::getDesign()->setArea('adminhtml');
	}
	
	protected function _runProfiles($interval){     				
		
		/*@var $collection Mage_Dataflow_Model_Mysql4_Profile_Collection */
		$collection = Mage::getResourceModel('dataflow/profile_collection')
						->addFieldToFilter('schedule', array('eq' => $interval))
						->addFieldToFilter('direction', array('eq' => 'export'));
						
		switch($interval){
			
			case 'minutely':
			    $collection->getSelect()->where('DATE_FORMAT(main_table.schedule_performed_at, "%i%Y") != DATE_FORMAT(NOW(), "%i%Y")');
				break;
		    
			case 'daily':
				$collection->getSelect()->where('DATE_FORMAT(main_table.schedule_performed_at, "%j%Y") != DATE_FORMAT(NOW(), "%j%Y")');
				break;
				
			case 'weekly':
				$collection->getSelect()->where('DATE_FORMAT(main_table.schedule_performed_at, "%u%Y") != DATE_FORMAT(NOW(), "%u%Y")');
				break;
				
			case 'monthly':
				$collection->getSelect()->where('DATE_FORMAT(main_table.schedule_performed_at, "%c%Y") != DATE_FORMAT(NOW(), "%c%Y")');
				break;							
		}	
			
		$collection->getSelect()->limit(1);
		$collection->load();

		if($collection->count()){

			$profile = $collection->getFirstItem();		
            $result = '<ul id="profileRows">';

            $profile->setSchedulePerformedAt(now())
            		->save();            
            
            $profile->run();
            foreach ($profile->getExceptions() as $e) {
                switch ($e->getLevel()) {
                    case Varien_Convert_Exception::FATAL:
                        $img = 'error_msg_icon.gif';
                        $liStyle = 'background-color:#FBB; ';
                        break;
                    case Varien_Convert_Exception::ERROR:
                        $img = 'error_msg_icon.gif';
                        $liStyle = 'background-color:#FDD; ';
                        break;
                    case Varien_Convert_Exception::WARNING:
                        $img = 'fam_bullet_error.gif';
                        break;
                    case Varien_Convert_Exception::NOTICE:
                        $img = 'fam_bullet_success.gif';
                        break;
                }
                $result .= '<li>';
                $result .= '<img src="'.$this->_getDesign()->getSkinUrl('images/'.$img).'" class="v-middle"/>';
                $result .= $e->getMessage();
                if ($e->getPosition()) {
                    $result .= " <small>(".$e->getPosition().")</small>";
                }
                $result .= "</li>";
            }
            $result .= "</ul>";	
                        
            $profile->getHistory()
            		->setResult($result)
            		->save();
		}
		
	}
}


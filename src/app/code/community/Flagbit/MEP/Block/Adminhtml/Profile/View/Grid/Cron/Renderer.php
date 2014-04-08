<?php

class   Flagbit_MEP_Block_Adminhtml_Profile_View_Grid_Cron_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if ($data = $row->getData('cron_expression')) {
            $type = $this->getColumn()->getIndex();
            $cronExpression = explode(' ', $data);
            if ($type == 'mep_cron_start_time')
            {
                $startTime = array($cronExpression[1], $cronExpression[0], '00');
                $data = implode(':', $startTime);
            }
            elseif ($type == 'mep_cron_frequency')
            {
                $frequencyStrings = Mage::getModel('adminhtml/system_config_source_cron_frequency')->toOptionArray();
                $frequencyDaily   = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
                $frequencyWeekly  = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
                $frequencyMonthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;
                $data = $frequencyDaily;
                if ($cronExpression[2] == '1') {
                    $data = $frequencyMonthly;
                }
                if ($cronExpression[4] == '1') {
                    $data = $frequencyWeekly;
                }
                foreach ($frequencyStrings as $frequencyString)
                {
                    if ($frequencyString['value'] == $data)
                    {
                        $data = ucfirst($frequencyString['label']);
                        break ;
                    }
                }
            }
            return $data;
        }
    }
}
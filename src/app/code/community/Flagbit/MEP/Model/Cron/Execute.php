<?php

class   Flagbit_MEP_Model_Cron_Execute {

    protected $_pendingSchedules;

    public function run() {
        $schedules = $this->_loadSchedulesCron();
        $observer = Mage::getModel('mep/observer');
        $ran = array();
        foreach ($schedules as $schedule) {
            $time = strtotime($schedule->getScheduledAt());
            $now = time();
            if ($time <= $now) {
                if (!in_array($schedule->getProfileId(), $ran)) {
                    $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_RUNNING);
                    $schedule->save();
                    $observer->runProfile($schedule->getProfileId());
                    $schedule->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
                    $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_SUCCESS);
                    $schedule->save();
                }
                else {
                    $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_MISSED);
                    $schedule->save();
                }
            }
        }
    }

    protected function  _loadSchedulesCron() {
        if (!$this->_pendingSchedules) {
            $this->_pendingSchedules = Mage::getModel('mep/cron')
                ->getCollection()
                ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_PENDING)
                ->addOrder('cron_id', Varien_Data_Collection_Db::SORT_ORDER_DESC)
                ->load();
        }
        return $this->_pendingSchedules;
    }
}
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
                    Mage::register('current_exporting_mep_profile', $schedule->getProfileId());
                    $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_RUNNING);
                    $schedule->save();
                    $observer->runProfile($schedule->getProfileId());
                    $schedule->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
                    $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_SUCCESS);
                    $schedule->save();
                    Mage::unregister('current_exporting_mep_profile');
                    $ran[] = $schedule->getProfileId(); //Important to prevent duplicated process
                }
                else {
                    $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_MISSED);
                    $schedule->save();
                }
            }
        }
        $this->_scheduleCron();
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

    protected function  _scheduleCron()
    {
        $profiles = Mage::getModel('mep/profile')
            ->getCollection()
            ->addFieldToFilter('status', array('eq' => '1'))
            ->addFieldToFilter('cron_activated', '1')
            ->load();
        foreach ($profiles as $profile)
        {
            $id = $profile->getId();
            $scheduleAheadFor = Mage::getStoreConfig(Mage_Cron_Model_Observer::XML_PATH_SCHEDULE_AHEAD_FOR) * 60;
            $schedule = Mage::getModel('mep/cron');
            $now = time() + 60;
            $timeAhead = $now + $scheduleAheadFor;

            $schedule->setCronExpr($profile->getCronExpression())
                ->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING)
                ->setProfileId($id)
                ->setIgnoreProfileStatus(0)
            ;

            $_errorMsg = null;
            for ($time = $now; $time < $timeAhead; $time += 60) {
                if (!$schedule->trySchedule($time)) {
                    // time does not match cron expression
                    continue;
                }
                if ($this->_alreadyScheduled($schedule))
                {
                    continue ;
                }
                $_errorMsg = null;
                $schedule->unsScheduleId()->save();
                break;
            }
        }
    }

    protected function  _alreadyScheduled($toSchedule)
    {
        $pending = $this->_loadSchedulesCron();
        foreach ($pending as $schedule)
        {
            if ($toSchedule->getProfileId() == $schedule->getProfileId() && $toSchedule->getScheduledAt() == $schedule->getScheduledAt())
            {
                return true;
            }
        }
        return false;
    }
}
<?php

class   Flagbit_MEP_Model_Cron_Execute {

    /**
     * @var Flagbit_MEP_Model_Cron
     */
    public static $currentSchedule = null;

    /**
     * config xpath to task Livetime
     */
    const XML_PATH_CRON_LIFETIME = 'mep/settings/task_lifetime';

    /**
     * run scheduled exports
     */
    public function run()
    {
        $now = time();
        $schedule = null;

        // register shutdown Handler for Error Handling
        register_shutdown_function(array('Flagbit_MEP_Model_Cron_Execute', 'shutdownHandler'));

        $schedules = Mage::getModel('mep/cron')
                        ->getCollection()
                        ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_PENDING)
                        ->addOrder('cron_id', Varien_Data_Collection_Db::SORT_ORDER_DESC)
                        ->load();

        // get the first schedule
        foreach($schedules as $schedule){

            // skip future tasks
            if (strtotime($schedule->getScheduledAt()) > $now) {
                continue;
            }

            if($schedule instanceof Flagbit_MEP_Model_Cron
                && $schedule->getId()){

                if($this->countRunningCron()){
                    Mage::helper('mep/log')->debug('Cannot run Profile '.$schedule->getProfileId().' because there is already a Job running', $this);
                    return;
                }

                Mage::helper('mep/log')->debug('CRON RUN Profile: ' . $schedule->getProfileId(), $this);

                // set current schedule to static variable so we can use it in shutdown handler
                self::$currentSchedule = $schedule;

                // set cron running
                $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_RUNNING)->save();

                // run Profile
                try{
                    Mage::getModel('mep/observer')->runProfile($schedule->getProfileId(), false);
                }catch (Exception $e){
                    $schedule->setStatus(Mage_Cron_Model_Schedule::STATUS_ERROR)
                    ->setLogs($e->getMessage())
                    ->save();
                }

                // set cron success
                $schedule->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                        ->setStatus(Mage_Cron_Model_Schedule::STATUS_SUCCESS)
                        ->save();
            }
            // only one task per instance to get right of limits and strange behavior
            break;
        }

        $this->_scheduleCron();
        $this->_removeExpiredTasks();
    }

    /**
     * count running lock Tasks
     *
     * @return int
     */
    public function countRunningCron(){

        $lifetime = Mage::getStoreConfig(self::XML_PATH_CRON_LIFETIME);

        $counter = Mage::getModel('mep/cron')->getCollection()
            ->addFieldToFilter('main_table.status', array('eq' => Mage_Cron_Model_Schedule::STATUS_RUNNING));

        if ($lifetime){
            $counter->addFieldToFilter('map.scheduled', array('gt' => time() - $lifetime));
        }
        return $counter->count();
    }

    /**
     * remove expired Tasks
     *
     * @return Flagbit_MEP_Model_Cron_Execute
     */
    protected function _removeExpiredTasks(){

        $lifetime = Mage::getStoreConfig(self::XML_PATH_CRON_LIFETIME);

        /*@var $collection Flagbit_MEP_Model_Mysql4_Cron_Collection */
        $collection = Mage::getModel('mep/cron')->getCollection();

        if ($lifetime){
            $collection->addFieldToFilter('map.scheduled', array('lt' => time() - $lifetime));
            $collection->addFieldToFilter('main_table.status', array('eq' => Mage_Cron_Model_Schedule::STATUS_RUNNING));

            $collection->setDataToAll('status', Mage_Cron_Model_Schedule::STATUS_MISSED);
            $collection->setDataToAll('messages', Mage::helper('mep')->__('automatically expired (TTL: %s)', $lifetime));
            $collection->save();
        }
        return $this;
    }


    /**
     * Hook to php shutdown handler
     *
     * This method is registered via register_shutdown_handler and
     * is used to grab fatal errors and handle them gracefully where
     * possible
     *
     * @return void
     */
    public static function shutdownHandler() {
       $error = error_get_last();
       if ($error && (
             ($error['type'] == E_ERROR) ||
             ($error['type'] == E_PARSE) ||
             ($error['type'] == E_RECOVERABLE_ERROR))) {
            $msg = $error['message'] . "\nLine: " . $error['line'] . ' - File: ' . $error['file'];

            if (class_exists('Mage')) {
                Mage::helper('mep/log')->err('ERROR Export: ' . $msg);

                if(is_object(self::$currentSchedule)){

                    self::$currentSchedule
                        ->setStatus(Mage_Cron_Model_Schedule::STATUS_ERROR)
                        ->setLogs($msg)
                        ->save();

                }
            }
       }
    }

    /**
     * create schedules
     */
    protected function _scheduleCron()
    {
        $profiles = Mage::getModel('mep/profile')
            ->getCollection()
            ->addFieldToFilter('status', array('eq' => '1'))
            ->addFieldToFilter('cron_activated', '1')
            ->load();

        foreach ($profiles as $profile)
        {
            $id = $profile->getId();
            $scheduleAheadFor = 60 * 60;
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

                Mage::helper('mep/log')->debug('SCHEDULE planned Profile ' .$id . ' on '.date('c', $time), $this);
                $schedule->unsScheduleId()->save();
                break;
            }
        }
    }

    /**
     * get if a schedule already planned
     *
     * @param $toSchedule
     * @return bool
     */
    protected function _alreadyScheduled($toSchedule)
    {
        $counter = Mage::getModel('mep/cron')->getCollection()
            ->addFieldToFilter('main_table.scheduled_at', array('eq' => $toSchedule->getScheduledAt()))
            ->addFieldToFilter('main_table.profile_id', array('eq' => $toSchedule->getProfileId()));

        return $counter->count() ? true : false;
    }
}
<?php

class Flagbit_MEP_Model_Cron extends Mage_Cron_Model_Schedule {

    public function __construct() {
        $this->_init('mep/cron');
    }
}
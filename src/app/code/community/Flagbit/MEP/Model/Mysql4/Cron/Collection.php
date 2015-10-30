<?php

class Flagbit_MEP_Model_Mysql4_Cron_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected $_map = array('fields' =>
        array(
            'map.scheduled' => 'UNIX_TIMESTAMP(scheduled_at)'
        ),
    );

    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('mep/cron');
    }

}
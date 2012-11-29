<?php
require_once 'abstract.php';

class Mage_Shell_Mep extends Mage_Shell_Abstract
{

    /**
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('runAll')) {
            /* @var $runner Flagbit_MEP_Model_Observer */
            $runner = Mage::getModel('mep/observer');
            $runner->exportEnabledProfiles();
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f mep.php -- [options]
        php -f mep.php --runAll

  runAll    Export all enabled profiles.
  help      This help

USAGE;
    }
}

$shell = new Mage_Shell_Mep();
$shell->run();

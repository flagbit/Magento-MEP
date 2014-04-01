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
        /** @var $runner Flagbit_MEP_Model_Observer */
        $runner = Mage::getModel('mep/observer');

        if($this->getArg('list')){
            echo sprintf('%-5s', 'ID');
            echo 'Name' . PHP_EOL;
            foreach($runner->getProfileCollection() as $profile){
                echo sprintf('%-5s', $profile->getId());
                echo $profile->getName() . PHP_EOL;
            }
        }elseif ($this->getArg('runAll')) {
            foreach($runner->getProfileCollection() as $profile){
                if($profile->hasData()){
                    Mage::register('current_exporting_mep_profile', $profile->getId());
                    $file = $runner->exportProfile($profile);
                    if($file){
                        Mage::helper('mep/log')->info('Profile "'.$profile->getName().'" successfully exported to: '.$file, $this);
                        echo 'Profile "'.$profile->getName().'" successfully exported to: '.$file.PHP_EOL;
                    }else{
                        Mage::helper('mep/log')->err('Profile "'.$profile->getName().'" export failed!', $this);
                        echo 'Profile "'.$profile->getName().'" export failed!'.PHP_EOL;
                    }
                    Mage::unregister('current_exporting_mep_profile');
                }
            }
        }elseif($this->getArg('runProfile')){
            $profile = Mage::getModel('mep/profile')->load($this->getArg('runProfile'));
            if($profile->hasData()){
                Mage::register('current_exporting_mep_profile', $profile->getId());
                $file = $runner->exportProfile($profile);
                if($file){
                    Mage::helper('mep/log')->info('Profile "'.$profile->getName().'" successfully exported to: '.$file, $this);
                    echo 'Profile "'.$profile->getName().'" successfully exported to: '.$file.PHP_EOL;
                }else{
                    Mage::helper('mep/log')->err('Profile "'.$profile->getName().'" export failed!', $this);
                    echo 'Profile "'.$profile->getName().'" export failed!'.PHP_EOL;
                }
            }
        } else {
            echo $this->usageHelp();
        }
    }


    /**
     * Parse .htaccess file and apply php settings to shell script
     * DISABLED
     */
    protected function _applyPhpVariables(){}

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f mep.php -- [options]
        php -f mep.php --runAll

  list                Show all enabled Profiles
  runAll              Export all enabled profiles.
  --runProfile <id>   Run specific Profile by ID
  help                This help

USAGE;
    }
}

$shell = new Mage_Shell_Mep();
$shell->run();

<?php

class Flagbit_MEP_Model_Profile extends Mage_Core_Model_Abstract
{
    const TWIG_TEMPLATE_TYPE_CONTENT = 'content';
    const TWIG_TEMPLATE_TYPE_HEADER = 'header';
    const TWIG_TEMPLATE_TYPE_FOOTER = 'footer';


    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('mep/profile');
    }

    /**
     * Processing object after loading
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function  _afterLoad() {
        $this->setSettings(unserialize($this->getSettings()));
        $cronExpression = $this->getCronExpression();
        if ($cronExpression) {
            //0 0 * * 1
            $cronExpression = explode(' ', $cronExpression);
            $startTime = array($cronExpression[1], $cronExpression[0], 0);
            $this->setMepCronStartTime(implode(',', $startTime));
            $frequencyDaily   = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
            $frequencyWeekly  = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
            $frequencyMonthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;
            if ($cronExpression[2] == '1') {
                $this->setMepCronFrequency($frequencyMonthly);
            }
            if ($cronExpression[4] == '1') {
                $this->setMepCronFrequency($frequencyWeekly);
            }
        }
        return parent::_afterLoad();
    }

    /**
     * Get quantity of products matching the profile
     *
     * @return integer
     */
    function getProductCount() {
        return $this->getData('product_count');
    }

    /**
     * Set number of products matching the profile
     */
    function setProductCount($product_count) {
        $this->setData('product_count', $product_count);
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if ($this->getId() || $this->getIsDuplicate() && $this->getOriginalId())
        {
            $this->setTwigHeaderTemplate(
                $this->_generateTemplate($this->getTwigHeaderTemplate(), self::TWIG_TEMPLATE_TYPE_HEADER)
            );
            $this->setTwigContentTemplate(
                $this->_generateTemplate($this->getTwigContentTemplate(), self::TWIG_TEMPLATE_TYPE_CONTENT)
            );
            $this->setSettings(serialize($this->getSettings()));
            if(!$this->getUseTwigTemplates()){
                $this->setTwigFooterTemplate('');
            }
            $time = $this->getData('mep_cron_start_time');
            $frequency = $this->getData('mep_cron_frequency');
            $frequencyDaily   = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
            $frequencyWeekly  = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
            $frequencyMonthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

            $cronExprArray = array(
                intval($time[1]),                                   # Minute
                intval($time[0]),                                   # Hour
                ($frequency == $frequencyMonthly) ? '1' : '*',      # Day of the Month
                '*',                                                # Month of the Year
                ($frequency == $frequencyWeekly) ? '1' : '*',       # Day of the Week
            );
            $cronExprString = join(' ', $cronExprArray);
            $this->setCronExpression($cronExprString);
        }
        return parent::_beforeSave();
    }


    /**
     * Create duplicate
     *
     * @return Flagbit_MEP_Model_Profile
     */
    public function duplicate()
    {
        /* @var $newProfile Flagbit_MEP_Model_Profile */
        $newProfile = Mage::getModel('mep/profile')->setData($this->getData())
            ->setIsDuplicate(true)
            ->setOriginalId($this->getId())
            ->setCreatedAt(null)
            ->setUpdatedAt(null)
            ->setFilename(null)
            ->setFilepatch(null)
            ->setId(null);

        Mage::dispatchEvent(
            'mep_model_profile_duplicate',
            array('current_profile' => $this, 'new_profile' => $newProfile)
        );

        $newProfile->save();

        /* @var $collection Flagbit_MEP_Model_Mysql4_Mapping_Collection */
        $collection = Mage::getModel('mep/mapping')->getCollection();
        $collection->addFieldToFilter('profile_id', array('eq' => $this->getId()))
            ->setOrder('position', 'ASC');

        foreach($collection as $mappingItem){
            $mappingItem->setId(null)
                ->setProfileId($newProfile->getId())
                ->save();
        }

        return $newProfile;
    }


    /**
     * generate Template
     *
     * @param $template
     * @param string $type
     * @return mixed|string
     */
    protected function _generateTemplate($template, $type = self::TWIG_TEMPLATE_TYPE_CONTENT)
    {
        $profileId = $this->getId() ? $this->getId() : $this->getOriginalId();

        /* @var $collection Flagbit_MEP_Model_Mysql4_Mapping_Collection */
        $collection = Mage::getModel('mep/mapping')->getCollection();
        $collection->addFieldToFilter('profile_id', array('eq' => $profileId))
            ->addAttributeSettings()
            ->setOrder('position', 'ASC');


        if($template && $this->getUseTwigTemplates()){
            // replace old missing Fields Hint
            $template = preg_replace('/(\R|)(\R|)\{\#\s--\s(.*)\s--\s(.*)\#\}(\R|)/ms', '', $template);

            // gen Array with missing Fields
            $newMappings = array();
            foreach($collection as $mapping){
                $field = $mapping->getData($type == self::TWIG_TEMPLATE_TYPE_HEADER ? 'to_field' : 'attribute_code');
                if(!empty($field) && strpos($template, $field) === false){
                    $newMappings[] = $mapping;
                }
            }
        }else{
            $newMappings = $collection;
        }

        $twigTemplateArray = array();
        foreach($newMappings as $mapping){
            $twigTemplateArray[] = $this->getEnclose().$this->_generateTemplateField($mapping, $type).$this->getEnclose();
        }
        // Template exists but there are new Fields
        if($template && count($twigTemplateArray) && $this->getUseTwigTemplates()){
            $template = $template.PHP_EOL.PHP_EOL.'{# -- '.Mage::helper('mep')->__('New fields which can not be automatically mapped').' -- '.PHP_EOL.implode($this->getDelimiter(), $twigTemplateArray).PHP_EOL.'#}';

            // Template does not exists but there are new Fields
        }elseif(!$template && count($twigTemplateArray)){
            $template = '{% spaceless %}'.PHP_EOL.implode($this->getDelimiter(), $twigTemplateArray).PHP_EOL.'{% endspaceless %}';
        }

        return $template;
    }

    /**
     * @param Flagbit_MEP_Model_Mapping $mapping
     * @param string $type
     * @return string
     */
    protected function _generateTemplateField(Flagbit_MEP_Model_Mapping $mapping, $type = self::TWIG_TEMPLATE_TYPE_CONTENT)
    {
        $_field = '';
        switch($type){

            case self::TWIG_TEMPLATE_TYPE_HEADER:
                $_field = $mapping->getToField();
                break;

            case self::TWIG_TEMPLATE_TYPE_CONTENT:
                $_modifier = array();
                switch($mapping->getBackendType()){

                    case 'decimal':
                        $_modifier = array('number_format_array(2, ",", ".")');
                        break;

                    case 'datetime':
                        $_modifier = array('date("d.m.Y")');
                        break;
                }

                $_field =  '{{ '.$mapping->getAttributeCode();
                $_field .= (count($_modifier) ? '|'.implode('|', $_modifier) : '');
                $_field .= ' }}';

                // handle mappings with format definition
                if($mapping->getFormat() || count($mapping->getAttributeCodeAsArray()) > 1){
                    $_field = '{{ "'.$mapping->getFormat().'"|format('.implode(',', $mapping->getAttributeCodeAsArray()).') }}';
                }

                // handle fixed value Format
                if($mapping->getAttributeCode() == 'fixed_value_format'){
                    $_field = $mapping->getFormat();
                }

                break;

        }


        return $_field;
    }

    public function uploadToFtp() {
        if ($this->getActivateFtp() == 1) {
            $hostPort = explode(':', $this->getFtpHostPort());
            if (empty($hostPort[0])) {
                return ;
            }
            if (empty($hostPort[1])) {
                $hostPort[1] = 21;
            }
            $args = array(
                'host' => trim($hostPort[0]),
                'port'  => trim($hostPort[1]),
                'user'  => $this->getFtpUser(),
                'password'  => $this->getFtpPassword(),
                'passive'   => true,
                'path'  => $this->getFtpPath(),
                'timeout'   => 5
            );
            $exportFile = $this->_getExportPath($this) . DS . $this->getFilename();
            try {
                $ftp = new Varien_Io_Ftp();
                $ftp->open($args);
                $ftp->write($this->getFilename(), $exportFile);
                $ftp->close();
            } catch (Varien_Io_Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    protected function _getExportPath($profile)
    {
        $exportDir = Mage::getConfig()->getOptions()->getBaseDir() . DS . $profile->getFilepath();

        if(Mage::getConfig()->getOptions()->createDirIfNotExists($exportDir) === FALSE){
            Mage::throwException('Export Directory is not writable ('.$exportDir.')');
        }

        return $exportDir;
    }
}
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
            if(!$this->getUseTwigTemplates()){
                $this->setTwigFooterTemplate('');
            }
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
                        $_modifier = array('number_format(2, ",", ".")');
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
}
<?php

class Flagbit_MEP_Model_Profil extends Mage_Core_Model_Abstract
{
    const TWIG_TEMPLATE_TYPE_CONTENT = 'content';
    const TWIG_TEMPLATE_TYPE_HEADER = 'header';
    const TWIG_TEMPLATE_TYPE_FOOTER = 'footer';


    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('mep/profil');
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if ($this->getId())
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
     * generate Template
     *
     * @param $template
     * @param string $type
     * @return mixed|string
     */
    protected function _generateTemplate($template, $type = self::TWIG_TEMPLATE_TYPE_CONTENT)
    {
        /* @var $collection Flagbit_MEP_Model_Mysql4_Mapping_Collection */
        $collection = Mage::getModel('mep/mapping')->getCollection();
        $collection->addFieldToFilter('profile_id', array('eq' => $this->getId()))
                   ->addAttributeSettings()
                   ->setOrder('position', 'ASC');


        if($template && $this->getUseTwigTemplates()){
            // replace old missing Fields Hint
            $template = preg_replace('/(\R|)(\R|)\{\#\s--\s(.*)\s--\s(.*)\#\}(\R|)/ms', '', $template);

            // gen Array with missing Fields
            $newMappings = array();
            foreach($collection as $mapping){
                if(strpos($template, $mapping->getToFieldNormalized()) === false){
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

                switch($mapping->getBackendType()){

                    case 'text':
                    case 'varchar':
                        $_field = '{{ '.$mapping->getToFieldNormalized().'|e }}';
                        break;

                    case 'int':
                    case 'decimal':
                        $_field = '{{ '.$mapping->getToFieldNormalized().'|number_format(2, ",", ".")|e }}';
                        break;

                    case 'datetime':
                        $_field = '{{ '.$mapping->getToFieldNormalized().'|date("d.m.Y")|e }}';
                        break;

                    default:
                        $_field = '{{ '.$mapping->getToFieldNormalized().'|e }}';
                        break;
                }

                // handle mappings with format definition
                if($mapping->getFormat()){
                    $_field = '{{ "'.$mapping->getFormat().'"|format('.$mapping->getToFieldNormalized().')|e }}';
                }
                break;

        }


        return $_field;
    }
}
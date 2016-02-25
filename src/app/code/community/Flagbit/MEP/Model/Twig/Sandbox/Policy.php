<?php

class Flagbit_MEP_Model_Twig_Sandbox_Policy extends Twig_Sandbox_SecurityPolicy
{

    public function __construct(array $allowedTags = array(), array $allowedFilters = array(), array $allowedMethods = array(), array $allowedProperties = array(), array $allowedFunctions = array())
    {
        $this->allowedTags = array('spaceless', 'if', 'set', 'block', 'do', 'filter', 'for');
        $this->allowedFilters = array('date', 'date_modify', 'format', 'replace', 'number_format', 'url_encode', 'json_encode', 'convert_encoding', 'title', 'capitalize', 'nl2br', 'upper', 'lower', 'striptags', 'join', 'split', 'reverse', 'abs', 'length', 'sort', 'default', 'keys', 'escape', 'raw', 'merge', 'slice', 'trim', 'last', 'first');
        $this->allowedMethods = array();
        $this->allowedProperties = array();
        $this->allowedFunctions = array('range', 'cycle', 'constant', 'random', 'block', 'date');
    }

    public function addAllowedTag($tag)
    {
        $this->allowedTags[] = $tag;
        return $this;
    }

    public function setAllowedFilter($filter)
    {
        $this->allowedFilters[] = $filter;
        return $this;
    }

    public function addAllowedFunction($function)
    {
        $this->allowedFunctions[] = $function;
        return $this;
    }

}
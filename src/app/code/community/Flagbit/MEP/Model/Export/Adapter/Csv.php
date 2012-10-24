<?php

class Flagbit_MEP_Model_Export_Adapter_Csv extends Mage_ImportExport_Model_Export_Adapter_Csv
{
    /**
     * Field delimiter.
     *
     * @var string
     */
    protected $_delimiter = ',';

    /**
     * Field enclosure character.
     *
     * @var string
     */
    protected $_enclosure = '"';


    /**
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->_delimiter = $delimiter;
    }

    /**
     * @param string $enclosure
     */
    public function setEnclosure($enclosure)
    {
        $this->_enclosure = $enclosure;
    }
}

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
     * Field headerrow.
     *
     * @var boolean
     */
    protected $_headerrow = true;

    /**
     * @param boolean $headerrow
     */
    public function setHeaderrow($headerrow)
    {
        $this->_headerrow = $headerrow;
    }


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

    public function setHeaderCols(array $headerCols)
    {

        if (null !== $this->_headerCols) {
            Mage::throwException(Mage::helper('importexport')->__('Header column names already set'));
        }
        if ($headerCols) {
            foreach ($headerCols as $colName) {
                $this->_headerCols[$colName] = false;
            }
            if ($this->_headerrow) {
                fputcsv($this->_fileHandler, array_keys($this->_headerCols), $this->_delimiter, $this->_enclosure);
            }
        }
        return $this;
    }

    /**
     * Get destination path.
     *
     * @return null|string
     */
    public function getDestination()
    {
        return $this->_destination;
    }
}

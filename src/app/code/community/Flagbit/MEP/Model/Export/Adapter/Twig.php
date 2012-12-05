<?php
/**
 * This file is part of the Flagbit MEP project.
 *
 * Flagbit MEP is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This script is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * PHP version 5
 *
 * @category Flagbit_MEP
 * @package Flagbit_MEP
 * @author Damian Luszczymak <damian.luszczymak@flagbit.de>
 * @author Karl Spies <karl.spies@flagbit.de>
 * @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
 * @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version 0.1.0
 * @since 0.1.0
 */
class Flagbit_MEP_Model_Export_Adapter_Twig extends Mage_ImportExport_Model_Export_Adapter_Csv
{
    /**
     * Field headerrow.
     *
     * @var boolean
     */
    protected $_headerRow = true;

    /**
     * @var Varien_File_Csv
     */
    protected $_csvWriter;

    /**
     * @param boolean $headerrow
     * @return \Flagbit_MEP_Model_Export_Adapter_Csv
     */
    public function setHeaderRow($headerrow)
    {
        $this->_headerRow = $headerrow;
        return $this;
    }

    public function _init()
    {
        parent::_init();
        $this->_csvWriter = new Varien_File_Csv();
    }


    /**
     * @param string $delimiter
     * @return \Flagbit_MEP_Model_Export_Adapter_Csv
     */
    public function setDelimiter($delimiter)
    {
        if ($delimiter == '\t') {
            $this->_delimiter = chr(9);
        } else {
            $this->_delimiter = $delimiter;
        }
        $this->_csvWriter->setDelimiter($this->_delimiter);
        return $this;
    }

    /**
     * @param string $enclosure
     * @return \Flagbit_MEP_Model_Export_Adapter_Csv
     */
    public function setEnclosure($enclosure)
    {
        $this->_enclosure = $enclosure;
        $this->_csvWriter->setEnclosure($this->_enclosure);
        return $this;
    }

    /**
     * Write row data to source file.
     *
     * @param array $rowData
     * @throws Exception
     * @return Mage_ImportExport_Model_Export_Adapter_Abstract
     */
    public function writeRow(array $rowData)
    {
        if (null === $this->_headerCols) {
            $this->setHeaderCols(array_keys($rowData));
        }

        $rowData = array_map(array($this, 'cleanLine'), $rowData);

        $this->_csvWriter->fputcsv(
            $this->_fileHandler,
            array_merge($this->_headerCols, array_intersect_key($rowData, $this->_headerCols)),
            $this->_delimiter,
            $this->_enclosure
        );

        return $this;
    }

    /**
     * Set column names.
     *
     * @param array $headerCols
     * @throws Exception
     * @return Mage_ImportExport_Model_Export_Adapter_Abstract
     */
    public function setHeaderCols(array $headerCols)
    {
        if (null !== $this->_headerCols) {
            Mage::throwException(Mage::helper('importexport')->__('Header column names already set'));
        }
        if ($headerCols) {
            foreach ($headerCols as $colName) {
                $this->_headerCols[$colName] = false;
            }
            $this->_csvWriter->fputcsv($this->_fileHandler, array_keys($this->_headerCols), $this->_delimiter, $this->_enclosure);
        }
        return $this;
    }

    public function cleanLine($element)
    {
        return trim(preg_replace('/\s\s+/', ' ', strip_tags(html_entity_decode($element))));
    }
}

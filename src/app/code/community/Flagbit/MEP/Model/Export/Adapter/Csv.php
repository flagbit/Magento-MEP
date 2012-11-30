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
    protected $_headerRow = true;

    /**
     * @param boolean $headerrow
     * @return \Flagbit_MEP_Model_Export_Adapter_Csv
     */
    public function setHeaderRow($headerrow)
    {
        $this->_headerRow = $headerrow;
        return $this;
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
        return $this;
    }

    /**
     * @param string $enclosure
     * @return \Flagbit_MEP_Model_Export_Adapter_Csv
     */
    public function setEnclosure($enclosure)
    {
        $this->_enclosure = $enclosure;
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

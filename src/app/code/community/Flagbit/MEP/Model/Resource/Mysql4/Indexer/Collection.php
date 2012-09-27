<?php
/**
 * This file is part of the Flagbit_CronCli project.
 *
 * Flagbit_CronCli is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This script is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * PHP version 5
 *
 * @category Flagbit_CronCli
 * @package Flagbit_CronCli
 * @author Damian Luszczymak <damian.luszczymak@flagbit.de>
 * @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
 * @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version 0.1.0
 * @since 0.1.0
 */
/**
 * Simple Collection
 *
 * @category Flagbit_CronCli
 * @package Flagbit_CronCli
 * @author Damian Luszczymak <damian.luszczymak@flagbit.de>
 * @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
 * @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version 0.1.0
 * @since 0.1.0
 */
class Flagbit_CronCli_Model_Resource_Mysql4_Indexer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    /**
     * Constructor
     *
     */
    protected function _construct() {
        $this->_init('croncli/indexer');
    }


    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Flagbit_CronCli_Model_Resource_Mysql4_Indexer_Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        $this->getSelect()->order($attribute. ' ' . $dir);
        return $this;
    }

    /**
     * @param $limit
     */
    public function setLimit($limit){
        $this->getSelect()->limitPage(0,10);
        return $this;
    }



}
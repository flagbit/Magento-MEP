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
 * Cron
 *
 * @category Flagbit_CronCli
 * @package Flagbit_CronCli
 * @author Damian Luszczymak <damian.luszczymak@flagbit.de>
 * @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
 * @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version 0.1.0
 * @since 0.1.0
 */
class Flagbit_CronCli_Model_Cron extends Mage_Core_Model_Abstract
{

    /*
     * 0 = wartet auf verarbeitung
     * 1 = wird verarbeitet
     * 2 = erfolgreich
     * 3 = fehlgeschlagen
     */


    public function indexer() {
        /** @var $obj_indexer Flagbit_CronCli_Model_Indexer */
        $obj_indexer = Mage::getModel('croncli/indexer');
        $indexer    = Mage::getSingleton('index/indexer');

        // Schaue ob schon in der Warteliste ist
        /**  @var $indexer_collection Flagbit_CronCli_Model_Resource_Mysql4_Indexer_Collection */
        $indexer_collection = $obj_indexer->getCollection();
        $indexer_collection
            ->addFieldToFilter('status',array('eq' => 0));

        // Wenn nicht dann trage den Indexer dort ein mit seiner ID

        foreach ($indexer_collection->getItems() as $item) {

            try {
                /** @var $item Flagbit_CronCli_Model_Indexer */
                $item->setStatus(1); // setze auf wird bearbeitet
                $item->save();

                /* @var $process Mage_Index_Model_Process */
                $process = $indexer->getProcessById($item->getIndexer());
                if ($process) {
                    $process->reindexEverything();
                    $item->setStatus(2);
                    $item->save();
                }
            }
            catch (Mage_Core_Exception $e) {
                $item->setStatus(3);
                $item->save();
            } catch (Exception $e) {
                $item->setStatus(3);
                $item->setError($e->getMessage());
                $item->save();
            }
        }
        if($indexer_collection->getSize() > 0){
            // Clean Cache
            Mage::app()->cleanCache();
        }

    }
}
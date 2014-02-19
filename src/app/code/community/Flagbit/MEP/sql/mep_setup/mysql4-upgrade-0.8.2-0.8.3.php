<?php
/**
 * This file is part of the Flagbit_MEP project.
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
 * @category Flagbit_MEP
 * @package Flagbit_MEP
 * @author Damian Luszczymak <damian.luszczymak@flagbit.de>
 * @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
 * @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version 0.1.0
 * @since 0.2.3
 */
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE `mep_cron` (
  `cron_id` INT NOT NULL AUTO_INCREMENT,
  `profile_id` INT NOT NULL,
  `ignore_profile_status` TINYINT NULL,
  `status` VARCHAR(45) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `scheduled_at` DATETIME NOT NULL,
  `finished_at` DATETIME NULL,
  `logs` TEXT NULL,
  PRIMARY KEY (`cron_id`));
");

$installer->endSetup();
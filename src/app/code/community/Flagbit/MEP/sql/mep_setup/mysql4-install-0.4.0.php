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
 * @since 0.1.0
 */
$installer = $this;
$installer->startSetup();

$installer->run("
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `{$this->getTable('mep/attribute_mapping')}`;
CREATE TABLE `{$this->getTable('mep/attribute_mapping')}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `attribute_code` varchar(255) NOT NULL,
  `source_attribute_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_type` enum('single', 'complete') DEFAULT 'single',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$this->getTable('mep/attribute_mapping_option')}`;
CREATE TABLE `{$this->getTable('mep/attribute_mapping_option')}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) NOT NULL,
  `store_id` int(10) NOT NULL,
  `option_id` int(10) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `mep_attribute_mapping_option_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `{$this->getTable('mep/attribute_mapping')}` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$this->getTable('mep/profile')}`;
CREATE TABLE `{$this->getTable('mep/profile')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `shipping_id` int(10) unsigned NOT NULL,
  `status` enum('0','1') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `conditions_serialized` mediumtext NOT NULL,
  `dataformat` int(11) NOT NULL,
  `originalrow` int(11) NOT NULL,
  `export` int(11) NOT NULL,
  `delimiter` varchar(15) DEFAULT NULL,
  `enclose` varchar(15) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `filepath` varchar(255) DEFAULT NULL,
  `category_delimiter` varchar(15) DEFAULT '/',
  `profile_locale` varchar(15) DEFAULT NULL,
  `twig_content_template` text NOT NULL,
  `twig_header_template` text NOT NULL,
  `twig_footer_template` text NOT NULL,
  `use_twig_templates` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$this->getTable('mep/mapping')}`;
CREATE TABLE `{$this->getTable('mep/mapping')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `attribute_code` varchar(255) NOT NULL,
  `to_field` text NOT NULL,
  `format` text NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`),
  CONSTRAINT `mep_profile_attribute_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `{$this->getTable('mep/profile')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$this->getTable('mep/shipping')}`;
CREATE TABLE `{$this->getTable('mep/shipping')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` enum('0','1') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$this->getTable('mep/shipping_attribute')}`;
CREATE TABLE `{$this->getTable('mep/shipping_attribute')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `shipping_method` text NOT NULL,
  `payment_method` text NOT NULL,
  `country` text NOT NULL,
  `attribute_code` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`),
  CONSTRAINT `mep_shipping_attribute_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `{$this->getTable('mep/shipping')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
");

$installer->endSetup();


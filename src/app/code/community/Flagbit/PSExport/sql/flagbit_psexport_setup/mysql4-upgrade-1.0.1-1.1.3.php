<?php 


$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$dataflowprofileTable = $installer->getTable('dataflow/profile');
$installer->getConnection()->addColumn($dataflowprofileTable, 'schedule_performed_at', "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");

$installer->endSetup();

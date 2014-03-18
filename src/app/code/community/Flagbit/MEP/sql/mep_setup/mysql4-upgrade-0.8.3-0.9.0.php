<?php

$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('mep/google_mapping')} (
`id` int(10) NOT NULL AUTO_INCREMENT,
`categorie_id` int(10) NOT NULL,
`google_mapping_ids` TEXT NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();

?>
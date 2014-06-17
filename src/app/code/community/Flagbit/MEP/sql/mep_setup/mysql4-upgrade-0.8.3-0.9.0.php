<?php

$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('mep/google_mapping')} (
`mapping_id` int(10) NOT NULL AUTO_INCREMENT,
`category_id` int(10) NOT NULL,
`google_mapping_ids` TEXT NOT NULL,
PRIMARY KEY (`mapping_id`),
UNIQUE (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();

?>
<?php

$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('mep/google_taxonomies')} (
`taxonomy_id` int(10) NOT NULL AUTO_INCREMENT,
`parent_id` int(10) NOT NULL,
`name` VARCHAR(255) NOT NULL,
`slug` VARCHAR(255) NOT NULL,
`locale` VARCHAR(10) NOT NULL,
PRIMARY KEY (`taxonomy_id`),
UNIQUE (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run(
    "ALTER TABLE `{$this->getTable('mep/profile')}` ADD COLUMN `use_single_process` int DEFAULT 0;"
);

$installer->endSetup();

?>

<?php 


$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$installer->addAttributeGroup('catalog_product', 'Default', 'PSExport');

$installer->addAttribute('catalog_product', 'psexport_name', array(
    'type'              => 'varchar',
    'backend'           => 'flagbit_psexport/entity_attribute_backend_name',
	'input_renderer'	=> 'flagbit_psexport/widget_form_element_dynamictext',
    'frontend'          => '',
    'label'             => 'PSExport Name',
    'input'             => 'text',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'group'             => 'PSExport',
    'note'              => 'Overwrites the default Name for Export',
));

$installer->addAttribute('catalog_product', 'psexport_description', array(
    'type'              => 'text',
    'backend'           => 'flagbit_psexport/entity_attribute_backend_description',
	'input_renderer'	=> 'flagbit_psexport/widget_form_element_dynamictext',
    'frontend'          => '',
    'label'             => 'PSExport Description',
    'input'             => 'textarea',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'group'             => 'PSExport',
    'note'              => 'Overwrites the default Description for Export',    
));

// update attributes group and sort
$attributes = array(
    'psexport_name'  => array(
        'group' => 'PSExport',
        'sort'  => 10
    ),
    'psexport_description'   => array(
        'group' => 'PSExport',
        'sort'  => 20
    ),
);

$dataflowprofileTable = $installer->getTable('dataflow/profile');
$installer->getConnection()->addColumn($dataflowprofileTable, 'schedule', 'varchar(10) NULL DEFAULT NULL');
$installer->getConnection()->addColumn($dataflowprofileTable, 'schedule_performed_at', "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");

$dataflowprofilehistoryTable = $installer->getTable('dataflow/profile_history');
$installer->getConnection()->addColumn($dataflowprofilehistoryTable, 'result', 'text NULL DEFAULT NULL');


$installer->endSetup();

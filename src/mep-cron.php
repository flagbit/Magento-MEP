<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

require 'app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app('admin')->setUseSessionInUrl(false);

// here we replace the cache and in the process we get rid of all loaded configuration, only
// core, core_resource and varien configuration is loaded after calling Mage::app() like this.
// we need this to avoid race conditions when using redis as cache, the multithreading export
// was having problems.
Mage::app('admin')->baseInit(array(
    'cache' => array(
        'backend' => 'Zend_Cache_Backend_BlackHole',
        'backend_options' => array()
    )
));

// now load all configurations again
Mage::app()->getConfig()
    ->loadModules()
    ->loadDb()
    ->saveCache();

umask(0);

try {
    $mepCron = Mage::getModel('mep/cron_execute');
    $mepCron->run();
}
catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}

<?php
/**
 * This file is part of the FIREGENTO project.
 *
 * FireGento_DynamicCategory is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This script is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * PHP version 5
 *
 * @category  FireGento
 * @package   FireGento_DynamicCategory
 * @author    FireGento Team <team@firegento.com>
 * @copyright 2012 FireGento Team (http://www.firegento.de). All rights served.
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version   1.0.0
 * @since     0.2.0
 */
/**
 * Rules for Conditions
 *
 * @category  FireGento
 * @package   FireGento_DynamicCategory
 * @author    FireGento Team <team@firegento.com>
 * @copyright 2012 FireGento Team (http://www.firegento.de). All rights served.
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version   1.0.0
 * @since     0.2.3
 */
class Flagbit_MEP_Model_Mysql4_Rule extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('mep/rule', 'rule_id');
    }

    public function afterLoad($object)
    {
        parent::afterLoad($object);
        $attrCode = 'conditions_serialized';
        $object->setData($attrCode, unserialize($object->getData($attrCode)));
    }

    /**
     * Before save method
     *
     * @param Varien_Object $object
     * @return void
     */
    public function beforeSave($object)
    {
        parent::beforeSave($object);
        $attrCode = 'conditions_serialized';
        $object->setData($attrCode, serialize($object->getData($attrCode)));
    }

}

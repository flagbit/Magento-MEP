<?php
/**
 * Helper
 *
 * @category Flagbit_MEP
 * @package Flagbit_MEP
 * @author Damian Luszczymak <damian.luszczymak@flagbit.de>
 * @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
 * @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version 0.1.0
 * @since 0.1.0
 */

class Flagbit_MEP_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param $profil_id
     * @param $template_id
     * @return bool
     */
    public function setTemplateProfil($profil_id, $template_id)
    {

        if ($profil_id > 0 && $template_id) {
            /* @var $transaction Mage_Core_Model_Resource_Transaction */
            $transaction = Mage::getResourceModel('core/transaction');
            $template_fields = array();

            if ($template_id == 1) {
                $template_fields[] = array('profile_id' => $profil_id,
                    'attribute_code' => 'sku',
                    'to_field' => 'Karl'
                );

                $template_fields[] = array('profile_id' => $profil_id,
                    'attribute_code' => 'name',
                    'to_field' => 'Karl2'
                );
            } elseif ($template_id == 2) {
                $template_fields[] = array('profile_id' => $profil_id,
                    'attribute_code' => 'sku',
                    'to_field' => 'Damian'
                );

                $template_fields[] = array('profile_id' => $profil_id,
                    'attribute_code' => 'name',
                    'to_field' => 'Damain2'
                );
            }

            foreach ($template_fields as $template) {
                /* @var $a Flagbit_MEP_Model_Mapping */
                $a = Mage::getModel('mep/mapping');
                $a->setData($template);
                $transaction->addObject($a);
            }


            try {
                $transaction->save();
                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            return false;
        }

    }
}
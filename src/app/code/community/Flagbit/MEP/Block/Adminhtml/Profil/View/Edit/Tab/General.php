<?php
class Flagbit_MEP_Block_Adminhtml_Profil_View_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * _prepareForm
     *
     * Prepares the edit form
     *
     * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
     *
     * @return Flagbit_MEP_Block_Adminhtml_View_Edit_Tab_General Self.
     */
    protected function _prepareForm()
    {
        if (Mage::getSingleton('adminhtml/session')->getMepProfileData()) {
            $data = Mage::getSingleton('adminhtml/session')->getMepProfileData();
        } elseif (Mage::registry('mep_profile_data')) {
            $data = Mage::registry('mep_profile_data')->getData();
        } else {
            $data = array();
        }

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'mep_profile_form',
            array(
                'legend' => Mage::helper('mep')->__('Profil')
            )
        );
        $fieldset->addField(
            'id',
            'label',
            array(
                'label' => Mage::helper('mep')->__('Profile ID'),
                'name' => 'id',
            )
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                'label' => Mage::helper('mep')->__('Profile Name'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'name',
            )
        );

        $fieldset->addField(
            'status',
            'select',
            array(
                'label' => Mage::helper('mep')->__('Status'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'status',
                'options' => $this->_getStatusOptionsHash()
            )
        );

        $fieldset->addField(
            'store_id',
            'select',
            array(
                'label'     => Mage::helper('mep')->__('Store View'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'store_id',
                'values'	=> $this->_getStoresOptionHash(),
            )
        );

        $form->setValues($data);
        return parent::_prepareForm();
    }


    protected function _getStatusOptionsHash()
    {
        $options = array(
            0 => Mage::helper('mep')->__('Disable'),
            1 => Mage::helper('mep')->__('Enable'),
        );
        return $options;
    }

    protected function _getStoresOptionHash()
    {
        $options = array();
        foreach ($websites = Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                foreach ($group->getStores() as $store) {
                    //$options[$store->getId()] = $store->getName();
                    if ($store->getIsActive() == 0) continue;
                    $wsName = $website->getName();
                    $stName = $group->getName();
                    $svName = $store->getName();
                    if (strlen($wsName) > 10) $wsName = substr($wsName, 0, 8) . '...';
                    if (strlen($stName) > 10) $stName = substr($stName, 0, 8) . '...';

                    $options[] = array(
                        'value' => $store->getId(),
                        'label' => $svName
                    );

                }
            }
        }
        return $options;
    }

}
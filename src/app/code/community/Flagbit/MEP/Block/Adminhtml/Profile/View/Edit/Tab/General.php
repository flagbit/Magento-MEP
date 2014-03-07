<?php
class Flagbit_MEP_Block_Adminhtml_Profile_View_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
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
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
            )
        );

        $optionFieldset = $form->addFieldset(
            'mep_profile_ftp_form',
            array(
                'legend' => Mage::helper('mep')->__('FTP Configuration') . ' <small><i>' . Mage::helper('mep')->__('To activate if you need to upload the export file to a FTP server') . '</i></small>'
            )
        );

        $optionFieldset->addField(
            'activate_ftp',
            'select',
            array(
                'label' => Mage::helper('core')->__('Activate'),
                'class' => 'required-entry',
                'required'  => true,
                'name'  => 'activate_ftp',
                'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toArray()
            )
        );

        $optionFieldset->addField(
            'ftp_host_port',
            'text',
            array(
                'label' => Mage::helper('mep')->__('FTP Host:Port'),
                'name'  => 'ftp_host_port',
                'value' => ':21',
                'note'  => Mage::helper('mep')->__('If no port given, port 21 will be used')
            )
        );

        $optionFieldset->addField(
            'ftp_user',
            'text',
            array(
                'label' => Mage::helper('mep')->__('FTP Username'),
                'name'  => 'ftp_user'
            )
        );

        $optionFieldset->addField(
            'ftp_password',
            'password',
            array(
                'label' => Mage::helper('mep')->__('FTP Password'),
                'name'  => 'ftp_password'
            )
        );

        $optionFieldset->addField(
            'ftp_path',
            'text',
            array(
                'label' => Mage::helper('mep')->__('Path on FTP server'),
                'name'  => 'ftp_path',
                'note'  => Mage::helper('mep')->__('If empty the root directory will be used')
            )
        );

        $cronSetting = $form->addFieldset(
            'mep_cron_setting_form',
            array(
                'legend' => 'Cron configuration'
            )
        );

        $cronSetting->addField(
            'cron_activated',
            'select',
            array(
                'label' => Mage::helper('mep')->__('Activate cron'),
                'name' => 'cron_activated',
                'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
            )
        );

        $cronSetting->addField(
            'mep_cron_start_time',
            'time',
            array(
                'label' => Mage::helper('mep')->__('Start Time'),
                'name' => 'mep_cron_start_time',
            )
        );

        $cronSetting->addField(
            'mep_cron_frequency',
            'select',
            array(
                'label' => Mage::helper('mep')->__('Frequency'),
                'name' => 'mep_cron_frequency',
                'values' => Mage::getModel('adminhtml/system_config_source_cron_frequency')->toOptionArray()
            )
        );

        $form->setValues(Mage::helper('mep')->getCurrentProfileData());
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
}
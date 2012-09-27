<?php

class Flagbit_PSExport_Block_System_Convert_Gui_Edit_Tab_History extends Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tab_History
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('history_grid');
        $this->setDefaultSort('performed_at', 'desc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('dataflow/profile_history_collection')
            ->joinAdminUser()
            ->addFieldToFilter('profile_id', Mage::registry('current_convert_profile')->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('action_code', array(
            'header'    => Mage::helper('adminhtml')->__('Profile Action'),
            'index'     => 'action_code',
            'filter'    => 'adminhtml/system_convert_profile_edit_filter_action',
            'renderer'  => 'adminhtml/system_convert_profile_edit_renderer_action',
        ));

        $this->addColumn('performed_at', array(
            'header'    => Mage::helper('adminhtml')->__('Performed At'),
            'type'      => 'datetime',
            'index'     => 'performed_at',
            'width'     => '130px',
        ));
        
        $this->addColumn('result', array(
            'header'    => Mage::helper('adminhtml')->__('Result'),
            'filter'    => false,
        	'renderer'  => 'flagbit_psexport/system_convert_gui_edit_grid_renderer_html',    
            'index'     => 'result',
        	'width'     => '500px',
        ));        

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('adminhtml')->__('First Name'),
            'index'     => 'firstname',
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('adminhtml')->__('Last Name'),
            'index'     => 'lastname',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/history', array('_current' => true));
    }
}

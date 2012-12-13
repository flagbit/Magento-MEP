<?php

class Flagbit_MEP_Block_Adminhtml_Profil_View_GridMapping extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Class Constructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('mapping_grid');
        $this->setUseAjax(true); // Using ajax grid is important
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(false); //Dont save paramters in session or else it creates problems
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild('addfilter_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label' => Mage::helper('adminhtml')->__('Add Attribute'),
                'onclick' => "mepTools.openDialog('".$this->getUrl('*/profil/popup', array('profile_id' => $this->getRequest()->getParam('profile_id', null)))."')",
                'class' => 'task'
            ))
        );
        return $this;
    }


    public function getAddfilterButtonHtml()
    {
        return $this->getChildHtml('addfilter_button');
    }


    public function getRowUrl($row)
    {
        return "javascript:mepTools.openDialog('".$this->getUrl('*/profil/popup', array('id' => $row->getId(), 'profile_id' => $this->getRequest()->getParam('profile_id', null)))."')";
    }


    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getAddfilterButtonHtml();

        return $html;
    }

    /**
     * _prepareCollection
     *
     * Prepares the collection for the grid
     *
     * @return Flagbit_MEP_Block_Adminhtml_Profil_View_Grid Self.
     */
    protected function _prepareCollection()
    {
        /* @var $collection Flagbit_MEP_Model_Mysql4_Mapping_Collection */
        $collection = Mage::getModel('mep/mapping')->getCollection();
        $profil_id = $this->getProfile();
        if (!empty($profil_id)) {
            $collection->addFieldToFilter('profile_id', array('eq' => $profil_id));
        }
        parent::setDefaultLimit('200');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);
        return $html;
    }


    /**
     * _prepareColumns
     *
     * Prepares the columns for the grid
     *
     * @return  Self.
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('mep')->__('ID'),
            'align' => 'left',
            'index' => 'id',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('attribute_code', array(
            'header' => Mage::helper('mep')->__('Attribute Code'),
            'align' => 'left',
            'index' => 'attribute_code',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('to_field', array(
            'header' => Mage::helper('mep')->__('To Field'),
            'align' => 'left',
            'index' => 'to_field',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('format', array(
            'header' => Mage::helper('mep')->__('Format'),
            'align' => 'left',
            'index' => 'format',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('position', array(
            'header' => Mage::helper('mep')->__('Position'),
            'align' => 'left',
            'index' => 'position',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('adminhtml')->__('Action'),
            'width' => '100px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('adminhtml')->__('Delete'),
                    'url' => array('base' => '*/profil_attribute/delete/profile_id/' . $this->getRequest()->getParam('profile_id')),
                    'field' => 'id',
                    'confirm' => $this->__('Do you really want to delete this field mapping.'),
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true,
        ));
        parent::_prepareColumns();
        return $this;
    }

    /**
     * _prepareMassaction
     *
     * Prepares the mass actions
     *
     * @return  Self.
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('mep')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('mep')->__('Are you sure?')
        ));


        return $this;
    }

    /**
     * call from ajax to get the grid
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}

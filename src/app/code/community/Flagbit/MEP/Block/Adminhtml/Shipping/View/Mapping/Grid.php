<?php

class Flagbit_MEP_Block_Adminhtml_Shipping_View_Mapping_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $this->setDefaultSort('position');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setPagerVisibility(false);
    }

    public function getProfileId()
    {
        return Mage::helper('mep')->getCurrentProfileData(true);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild('addfilter_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label' => Mage::helper('adminhtml')->__('Add Attribute'),
                'onclick' => "mepTools.openDialog('".$this->getUrl('*/shipping/popup', array('profile_id' => $this->getProfileId()))."')",
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
        return "javascript:mepTools.openDialog('".$this->getUrl('*/shipping/popup', array('id' => $row->getId(), 'profile_id' => $this->getProfileId()))."')";
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html  = '<div id="messages">'.$this->getMessagesBlock()->getGroupedHtml().'</div>';
        $html .= parent::_toHtml();
        return $html;
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
     * @return Flagbit_MEP_Block_Adminhtml_Profile_View_Grid Self.
     */
    protected function _prepareCollection()
    {
        /* @var $collection Flagbit_MEP_Model_Mysql4_Shipping_Attribute */
        $collection = Mage::getModel('mep/shipping_attribute')->getCollection();
        $collection->addFieldToFilter('profile_id', array('eq' => $this->getProfileId()));

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

        $this->addColumn('shipping_method', array(
            'header' => Mage::helper('mep')->__('Shipping Method'),
            'align' => 'left',
            'index' => 'shipping_method',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('payment_method', array(
            'header' => Mage::helper('mep')->__('Payment Method'),
            'align' => 'left',
            'index' => 'payment_method',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('country', array(
            'header' => Mage::helper('mep')->__('Country'),
            'align' => 'left',
            'index' => 'country',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('adminhtml')->__('Action'),
            'width' => '100px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('adminhtml')->__('Delete'),
                    'url' => array('base' => '*/shipping_attribute/delete/profile_id/' . $this->getRequest()->getParam('profile_id')),
                    'field' => 'mapping_id',
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
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('mapping_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('mep')->__('Delete'),
            'url' => $this->getUrl('*/shipping_attribute/massDelete', array('_current' => true)),
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

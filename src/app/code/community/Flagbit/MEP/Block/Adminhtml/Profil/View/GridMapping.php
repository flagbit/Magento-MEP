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
                'onclick' => 'doFieldMapping()',
                'class' => 'task'
            ))
        );
        return $this;
    }


    public function getAddfilterButtonHtml()
    {
        return $this->getChildHtml('addfilter_button');
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
        /* @var $collection Flagbit_MEP_Model_Profil */
        $collection = Mage::getModel('mep/mapping')->getCollection();
        $profil_id = $this->getProfile();
        if (!empty($profil_id)) {
            $collection->addFieldToFilter('profile_id', array('eq' => $profil_id));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterToHtml($html){
        $html = parent::_afterToHtml($html);
        $aha = Mage::getModel('mep/data')->getExternalAttributes();
        $html .= '
        <div id="container" style="display:none">
        	   <div id="test_content" style="float:right;width:200px; height:250px;background:#DFA; color:#000; font-size:12px;">
        		  <form action="'.Mage::getUrl("adminhtml/profil/attribute").'" id="mappingform"> In Database:';

                   $html .= '<select name="attribute_code"">';
            foreach ($aha as $_value=>$_label){


            if(is_array($_label)){
            $html .= '<optgroup label='.$_value.'">';
            foreach($_label as $_attribute){
                //$html .= '<option value="'.$_value.':'.$_attribute.'">'.$_attribute.'</option>';
                $html .= '<option value="'.$_attribute.'">'.$_attribute.'</option>';

            }

            $html .= '</optgroup>';
            }
            else{
            $html .= '<option value="'.$_value.'">'.$_label.'</option>';
            }

            }
            $html .= '</select><br/>
To Field<br/>
<input type="text" name="to_field"><br/>
Format<br/>
<input type="text" name="format"><br/>
<input type="submit" value=" Absenden ">
<input type="hidden" name="profile_id" value="'.Mage::app()->getRequest()->getParam('id').'">
</form>
        	  </div>
      	</div>


<script type="text/javascript">
// <![CDATA[
    var contentWin = null;

    var doFieldMapping = function (){
    if (contentWin != null) {
  Dialog.alert("Close the Mapping Field Window before opening it again!",{width:200, height:130});
}
else {
  $("container").show();
  contentWin = new Window({maximizable: false, resizable: false, hideEffect:Element.hide, showEffect:Element.show, minWidth: 10, destroyOnClose: true})
  contentWin.setContent("test_content", true, true)
  contentWin.show();

  // Set up a windows observer, check ou debug window to get messages
  myObserver = {
    onDestroy: function(eventName, win) {
      if (win == contentWin) {
        $("container").hide();
        $("container").appendChild($("test_content"));
        contentWin = null;
        Windows.removeObserver(this);
      }
      console.log(eventName + " on " + win.getId())
    }
  }
  Windows.addObserver(myObserver);
}
}
window.doFieldMapping = doFieldMapping;


Event.observe("mappingform", "submit", function(event) {
    $("mappingform").request({
        onFailure: function() { alert("Error beim speichern") },
        onSuccess: function(t) {
            alert("Gespeichert")
        }
    });
    Event.stop(event); // stop the form from submitting
});


// ]]>
</script>';
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
        ));
        $this->addColumn('attribute_code', array(
            'header' => Mage::helper('mep')->__('Attribute Code'),
            'align' => 'left',
            'index' => 'attribute_code',
        ));

        $this->addColumn('to_field', array(
            'header' => Mage::helper('mep')->__('To Field'),
            'align' => 'left',
            'index' => 'to_field',
        ));

        $this->addColumn('format', array(
            'header' => Mage::helper('mep')->__('Format'),
            'align' => 'left',
            'index' => 'format',
            'filter' => false,
        ));


        $this->addColumn('action', array(
            'header' => Mage::helper('adminhtml')->__('Action'),
            'width' => '100px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('adminhtml')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id',
                ),
                array(
                    'caption' => Mage::helper('adminhtml')->__('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                ),

            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
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
     * Returns the row url
     *
     * @return string URL
     */
    public function getRowUrl($row)
    {
        $url = $this->getUrl('*/*/edit', array('id' => $row->getId()));
        return $url;
    }
}
<?php

class Flagbit_PSExport_Block_Widget_Form_Element_Dynamictext extends Varien_Data_Form_Element_Editor {
	
	public function getElementHtml() {

        $js = '
            <script type="text/javascript">
            //<![CDATA[
				
            	Variables.openDialogWindow = function(variablesContent) {
			        if ($(this.dialogWindowId) && typeof(Windows) != \'undefined\') {
			            Windows.focus(this.dialogWindowId);
			            return;
			        }
			
			        this.overlayShowEffectOptions = Windows.overlayShowEffectOptions;
			        this.overlayHideEffectOptions = Windows.overlayHideEffectOptions;
			        Windows.overlayShowEffectOptions = {duration:0};
			        Windows.overlayHideEffectOptions = {duration:0};

			        this.dialogWindow = Dialog.info(variablesContent, {
			            draggable:true,
			            resizable:true,
			            closable:true,
			            className:"magento",
			            windowClassName:"popup-window",
			            title:\'Insert Variable...\',
			            width:700,
			            height: (Variables.height ? Variables.height : null),
			            zIndex:1000,
			            recenterAuto:false,
			            hideEffect:Element.hide,
			            showEffect:Element.show,
			            id:this.dialogWindowId,
			            onClose: this.closeDialogWindow.bind(this)
			        });
			        variablesContent.evalScripts.bind(variablesContent).defer();
			    }  
			         	
				DynamicTextWindow = {
				    variables: null,
				    textareaId: null,
				    loadChooser: function(url, textareaId) {
				        this.textareaId = textareaId;
				        if (this.variables == null) {
				            new Ajax.Request(url, {
				                parameters: {},
				                onComplete: function (transport) {
				                    if (transport.responseText.isJSON()) {
				                        Variables.init(null, \'DynamicTextWindow.insertVariable\');
				                        this.variables = transport.responseText.evalJSON();
				                        this.openChooser(this.variables);
				                    }
				                }.bind(this)
				             });
				        } else {
				            this.openChooser(this.variables);
				        }
				        return;
				    },
				    openChooser: function(variables) {
				    	Variables.height = 400;
				        Variables.openVariableChooser(variables);
				        Variables.height = null;
				    },
				    insertVariable : function (value) {
				        if (this.textareaId) {
				            Variables.init(this.textareaId);
				            Variables.insertVariable(value);
				        }
				        return;
				    }
				};
            
            //]]>
            </script>';		
		
		$this->setConfig ( Mage::getSingleton ( 'cms/wysiwyg_config' )->getConfig () );
		
		$html = $this->_getButtonsHtml (). Varien_Data_Form_Element_Textarea::getElementHtml ();
		$html = $this->_wrapIntoContainer ( $html );
		return $js.$html;
	
	}

    /**
     * Check whether Wysiwyg is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return false;
    }	

    /**
     * Prepare Html buttons for additional WYSIWYG features
     *
     * @param bool $visible Display button or not
     * @return void
     */
    protected function _getPluginButtonsHtml($visible = true)
    {
        $buttonsHtml = '';

        // Button to widget insertion window

            $buttonsHtml .= $this->_getButtonHtml(array(
                'title'     => $this->translate('Insert Dynamic Text...'),
                'onclick'   => "DynamicTextWindow.loadChooser('" .Mage::getSingleton('adminhtml/url')->getUrl('*/dynamictext/index') . "','".$this->getHtmlId() ."');",
                'class'     => 'scalable add-variable plugin',
                'style'     => $visible ? '' : 'display:none',
            ));



        return $buttonsHtml;
    }
	
}

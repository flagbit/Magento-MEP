var mepTools = {
    getDivHtml: function(id, html) {
        if (!html) html = '';
        return '<div id="' + id + '">' + html + '</div>';
    },

    onAjaxSuccess: function(transport) {
        if (transport.responseText.isJSON()) {
            var response = transport.responseText.evalJSON()
            if (response.error) {
                throw response;
            } else if (response.ajaxExpired && response.ajaxRedirect) {
                setLocation(response.ajaxRedirect);
            }
        }
    },

    openDialog: function(widgetUrl) {
        if ($('widget_window') && typeof(Windows) != 'undefined') {
            Windows.focus('widget_window');
            return;
        }
        this.dialogWindow = Dialog.info(null, {
            draggable:true,
            resizable:false,
            closable:true,
            className:'magento',
            windowClassName:"popup-window",
            title:Translator.translate('Insert Widget...'),
            top:50,
            width:400,
            //height:450,
            zIndex:1000,
            recenterAuto:false,
            hideEffect:Element.hide,
            showEffect:Element.show,
            id:'widget_window',
            onClose: this.closeDialog.bind(this)
        });
        new Ajax.Updater('modal_dialog_message', widgetUrl, {evalScripts: true});
    },
    closeDialog: function(window) {
        if (!window) {
            window = this.dialogWindow;
        }
        if (window) {
            // IE fix - hidden form select fields after closing dialog
            WindowUtilities._showSelect();
            window.close();
        }
    }
}

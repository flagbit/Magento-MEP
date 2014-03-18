var GoogleMapping = Class.create();

GoogleMapping.prototype = {
    initialize : function(requestUrl) {
        var instance = this;
        this.options = {};
        this.options.requestUrl = requestUrl;
        this.options.storeSelector = $('store_selection_select');
        this.options.categoriesBlock = $('categories_list');
        this.options.storeId = this.options.storeSelector.value;
        this.options.storeSelector.observe('change', function() {
            instance.options.storeId = instance.options.storeSelector.value;
            instance.load();
        });
    },
    load : function() {
        var instance = this;
        new Ajax.Request(this.options.requestUrl + '?store_id=' + this.options.storeId, {
            method: 'get',
            onSuccess: function(transport) {
                instance.options.categoriesBlock.innerHTML = transport.responseText;
            }
        });
    }
}
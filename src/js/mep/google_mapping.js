var GoogleMapping = Class.create();

GoogleMapping.prototype = {
    initialize : function() {
        var instance = this;
        this.options = {};
        this.options.requestUrl = {};
        this.loadedSelect = {};
        this.currentCategory = null;
        this.currentTaxonomy = null;
        this.options.storeSelector = $('store_selection_select');
        this.options.categoriesBlock = $('categories_list');
        this.options.storeId = this.options.storeSelector.value;
        this.options.selectClass = '.taxonomy-select';
        this.options.storeSelector.observe('change', function() {
            instance.options.storeId = instance.options.storeSelector.value;
            instance.load();
        });
    },
    load : function() {
        var instance = this;
        new Ajax.Request(this.options.requestUrl.loadcategories + '?store_id=' + this.options.storeId, {
            method: 'get',
            onSuccess: function(transport) {
                instance.options.categoriesBlock.innerHTML = transport.responseText;
                instance.bindSelect();
            }
        });
    },
    loadTaxonomy : function() {
        var instance = this;
        var taxonomyId = this.currentTaxonomy;
        if (this.loadedSelect[taxonomyId] != undefined) {
            this.generateSelect();
            this.bindSelect();
            return ;
        }
        new Ajax.Request(this.options.requestUrl.loadtaxonomies + '?taxonomy_id=' + taxonomyId, {
            method: 'get',
            onSuccess: function(transport) {
                instance.loadedSelect[taxonomyId] = eval(transport.responseText);
                instance.generateSelect();
                instance.bindSelect();
            }
        });
    },
    bindSelect: function() {
        var instance = this;
        $$(this.options.selectClass).invoke('stopObserving', 'change');
        $$(this.options.selectClass).invoke('observe', 'change', function()
        {
            instance.currentTaxonomy = this.value;
            instance.currentCategory = this.classNames().element.className.replace('taxonomy-select ', '');
            instance.getSelectForTaxonomy();
        })
    },
    getSelectForTaxonomy : function() {
        this.loadTaxonomy();
    },
    generateSelect : function() {
        var select = new Element('select', {class: 'taxonomy-select ' + this.currentCategory});
        select.insert(new Element('option'));
        for (var i = 0; i < this.loadedSelect[this.currentTaxonomy].length; i++) {
            select.insert(new Element('option', {value: this.loadedSelect[this.currentTaxonomy][i].value}).update(this.loadedSelect[this.currentTaxonomy][i].label));
        }
        $(this.currentCategory).insert({
            bottom: select
        });
    }
}
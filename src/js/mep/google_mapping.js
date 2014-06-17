var GoogleMapping = Class.create();

GoogleMapping.prototype = {
    initialize : function() {
        var instance = this;
        this.options = {};
        this.options.requestUrl = {};
        this.loadedSelect = {};
        this.currentCategory = null;
        this.currentTaxonomy = null;
        this.currentLevel = null;
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
            parameters: {
                evalJS: true
            },
            onSuccess: function(transport) {
                instance.parseJson(transport.responseText);
                instance.bindSelect();
            }
        });
    },
    parseJson : function(text) {
        this.options.categoriesBlock.update();
        var json = eval(text);
        for (var i = 0; i < json.length; i++) {
            var current = json[i];
            var div = new Element('div', {id: 'category-' + current.id, class: 'mep_category_list_item', style: 'margin-left:' + current.margin + 'px'}).update(current.name);
            this.options.categoriesBlock.insert({
                bottom: div
            });
            this.currentCategory = 'category-' + current.id;
            for (var y = 0; y < current.mapping.length; y++)
            {
                this.currentTaxonomy = current.mapping[y].taxonomyId;
                this.currentLevel = parseInt(current.mapping[y].level) - 1;
                this.loadedSelect[current.mapping[y].taxonomyId] = current.mapping[y].options;
                this.getSelectForTaxonomy();
                if ($$('.' + this.currentCategory + '.level-' + current.mapping[y].level).length > 0) {
                    $$('.' + this.currentCategory + '.level-' + current.mapping[y].level).first().value = current.mapping[y].value;
                }
            }
        }
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
            instance.currentCategory = this.classNames().element.className.match(/category-[0-9]+/)[0];
            instance.currentLevel = parseInt(this.classNames().element.className.match(/level-([0-9])+/)[1]);
            if (instance.currentTaxonomy.length == 0) {
                instance.removeExistingLevels();
            }
            else {
                instance.getSelectForTaxonomy();
            }
        })
    },
    getSelectForTaxonomy : function() {
        this.loadTaxonomy();
    },
    generateSelect : function() {
        this.removeExistingLevels();
        if (this.loadedSelect[this.currentTaxonomy].length) {
            var categoryId = this.currentCategory.match(/[0-9]+/);
            var currentLevel = this.currentLevel + 1;
            var selectName = 'google-mapping[' + categoryId + '][' + currentLevel + ']';
            var select = new Element('select', {name: selectName, class: 'taxonomy-select level-' + currentLevel + ' ' + this.currentCategory});
            select.insert(new Element('option'));
            for (var i = 0; i < this.loadedSelect[this.currentTaxonomy].length; i++) {
                select.insert(new Element('option', {value: this.loadedSelect[this.currentTaxonomy][i].value}).update(this.loadedSelect[this.currentTaxonomy][i].label));
            }
            $(this.currentCategory).insert({
                bottom: select
            });
        }
    },
    removeExistingLevels : function() {
        var level = this.currentLevel + 1;
        var existing = $$('.' + this.currentCategory + '.level-' + level).first();
        while (existing != undefined) {
            existing.remove();
            level++;
            existing = $$('.' + this.currentCategory + '.level-' + level).first();
        }
    }
}
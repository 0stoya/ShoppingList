define([
    'uiComponent',
    'ko',
    'jquery',
    'mage/url',
    'uiRegistry'
], function(Component, ko, $, urlBuilder, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            template: null,
            listId: null,
            url: null,
            items: []
        },

        initialize: function() {
            this._super();
            this.items = ko.observableArray([]);
            this.loadItems();
        },

        loadItems: function() {
            $.ajax({
                url: urlBuilder.build(this.url),
                data: { list_id: this.listId },
                type: 'GET'
            }).done(function(res) {
                this.items(res.items.map(function(i) {
                    return {
                        itemId: i.item_id,
                        name: i.product_name,
                        qty: ko.observable(i.qty)
                    };
                }));
            }.bind(this));
        },

        removeItem: function(item) {
            $.ajax({
                url: urlBuilder.build('shoppinglist/list/apiRemove'),
                type: 'POST',
                data: { item_id: item.itemId }
            }).done(function() {
                this.items.remove(item);
            }.bind(this));
        },

        updateItem: function(item) {
            $.ajax({
                url: urlBuilder.build('shoppinglist/list/apiUpdate'),
                type: 'POST',
                data: { item_id: item.itemId, qty: item.qty() }
            });
        },

        addAllToCart: function() {
            $.ajax({
                url: urlBuilder.build('shoppinglist/list/addtocart'),
                type: 'POST',
                data: { list_id: this.listId }
            }).done(function() {
                registry.get('index = messages', function(messages) {
                    messages.clear();
                    messages.addSuccessMessage({message: 'Added all items to cart'});
                });
            });
        }
    });
});

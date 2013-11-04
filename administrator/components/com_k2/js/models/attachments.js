define(['backbone'], function(Backbone) {'use strict';

	var K2ModelAttachments = Backbone.Model.extend({
		initialize : function() {
			this.set('cid', this.cid);
		},
		defaults : {
			id : null,
			itemId : null,
			name : null,
			title : null,
			file : null,
			url : null,
			downloads : 0
		},
		urlRoot : 'index.php?option=com_k2&task=attachments.sync&format=json',
		url : function() {
			var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
			if (this.isNew())
				return base;
			return base + '&id=' + encodeURIComponent(this.id);
		},
		sync : function(method, model, options) {
			// Convert any model attributes to data if options data is empty
			options.data = [];
			_.each(model.attributes, function(value, attribute) {
				options.data.push({
					name : attribute,
					value : value
				});
			});
		}
	});

	return K2ModelAttachments;

});

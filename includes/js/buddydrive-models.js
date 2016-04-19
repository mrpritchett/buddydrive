/* globals wp, bp, buddydrive, BuddyDrive_App, BP_Uploader, _, Backbone */
window.wp         = window.wp || {};
window.bp         = window.bp || {};
window.buddydrive = window.buddydrive || {};

( function() {

	if ( typeof BuddyDrive_App === 'undefined' ) {
		return false;
	}

	// Use WordPress if BuddyPress is not set
	if ( typeof BP_Uploader === 'undefined' ) {
		_.extend( buddydrive, _.pick( wp, 'Backbone', 'ajax', 'template' ) );
	} else {
		_.extend( buddydrive, bp );
	}

	// Init Models and Collections
	buddydrive.Models      = buddydrive.Models || {};
	buddydrive.Collections = buddydrive.Collections || {};
	buddydrive.Settings    = BuddyDrive_App.settings || {};
	buddydrive.Strings     = BuddyDrive_App.strings || {};

	/** Models ***********************************************************/

	buddydrive.Models.Query = Backbone.Model.extend( {
		defaults: {
			paged: 1,
			buddydrive_scope: 'public',
			search: ''
		}
	} );

	buddydrive.Models.Item = Backbone.Model.extend( {
		defaults: {
			id           : 0,
			user_id      : 0,
			privacy      : 'public',
			link         : '',
			type         : '',
			date_created : 0,
			date_edited  : 0
		},

		update: function( data, options ) {
			options         = options || {};
			options.context = this;
			options.data    = data || {};

			options.data = _.extend( options.data, {
				action             : 'buddydrive_item_update',
				'buddydrive_nonce' : buddydrive.Settings.nonces.update_item
			} );

			return buddydrive.ajax.send( options );
		}
	} );

	// Item (user, group or blog, or potentially any other)
	buddydrive.Models.bpObject = Backbone.Model.extend( {
		defaults: {
			id              : 0,
			buddydrive_type : 'group',
			name            : '',
			link            : '',
			avatar          : ''
		}
	} );

	// Stats for the user
	buddydrive.Models.Stat = Backbone.Model.extend( {
		defaults: { id : 0, used: 0 },

		stat: function( options ) {
			options = _.extend( options || {}, {
				context : this,
				success : this.populate
			} );

			options.data = _.extend( options.data || {}, {
				action             : 'buddydrive_get_stats',
				'buddydrive_nonce' : buddydrive.Settings.nonces.user_stats
			} );

			return buddydrive.ajax.send( options );
		},

		populate: function( model ) {
			this.set( model );
		}
	} );

	/** Collections ***********************************************************/

	buddydrive.Collections.Items = Backbone.Collection.extend( {
		model: buddydrive.Models.Item,

		initialize : function() {
			this.options = { paged: 1, 'has_more_items': false };
		},

		sync: function( method, model, options ) {
			options         = options || {};
			options.context = this;
			options.data    = options.data || {};

			if ( 'read' === method ) {
				options.data = _.extend( options.data, {
					action             : 'buddydrive_fetch_items',
					'buddydrive_nonce' : buddydrive.Settings.nonces.fetch_items
				} );

				return buddydrive.ajax.send( options );
			}
		},

		bulkEdit: function( options ) {
			options = _.extend( options || {}, {
				context : this,
				success : this.doBulk
			} );

			options.data = _.extend( options.data || {}, {
				action             : 'buddydrive_bulk_edit_items',
				'buddydrive_nonce' : buddydrive.Settings.nonces.bulk_edit
			} );

			return buddydrive.ajax.send( options );
		},

		doBulk: function( models ) {
			this.remove( models );
		},

		addFolder: function( options ) {
			options = _.extend( options || {}, {
				context : this,
				success : this.insertFolder
			} );

			options.data = _.extend( options.data || {}, {
				action             : 'buddydrive_add_folder',
				'buddydrive_nonce' : buddydrive.Settings.nonces.new_folder
			} );

			return buddydrive.ajax.send( options );
		},

		insertFolder: function( model ) {
			this.add( model );
		},

		parse: function( resp ) {
			var items    = resp.items;
			this.options = resp.metas || this.options;

			if ( ! _.isArray( items ) ) {
				items = [items];
			}

			_.each( items, function( value, index ) {
				if ( _.isNull( value ) ) {
					return;
				}

				items[index].date_created = new Date( value.date_created );
				items[index].date_edited  = new Date( value.date_edited );
			} );

			return items;
		}
	} );

	// Items (users, groups or blogs or potentially any others)
	buddydrive.Collections.bpObjects = Backbone.Collection.extend( {
		model: buddydrive.Models.bpObject,

		sync: function( method, model, options ) {

			if ( 'read' === method ) {
				options = options || {};
				options.context = this;
				options.data = _.extend( options.data || {}, {
					action             : 'buddydrive_get_bpobjects',
					'buddydrive_nonce' : buddydrive.Settings.nonces.fetch_objects
				} );

				return buddydrive.ajax.send( options );
			}
		},

		parse: function( resp, xhr ) {
			if ( ! _.isArray( resp ) ) {
				resp = [resp];
			}

			// If the item's is already attached, select it.
			if ( ! _.isUndefined( xhr.data.existing ) ) {
				_.each( resp, function( item ) {
					item.selected = true;
				} );
			}

			return resp;
		}
	} );

} )( buddydrive );

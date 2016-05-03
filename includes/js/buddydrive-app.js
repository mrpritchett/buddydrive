/* globals buddydrive, _, Backbone */
window.buddydrive = window.buddydrive || {};

( function( exports, $ ) {

	/**
	 * Main App
	 * @type {Object}
	 */
	buddydrive.App = {

		start: function() {
			this.views      = new Backbone.Collection();
			this.items      = new buddydrive.Collections.Items();
			this.router     = new buddydrive.App.Router();
			this.Query      = new buddydrive.Models.Query( _.pick( buddydrive.Settings, 'buddydrive_scope' ) );

			// Check available width
			if ( 1000 > $( '#buddydrive-main' ).width() ) {
				$( '#buddydrive-main' ).addClass( 'mini' );
			}

			Backbone.history.start();
		},

		listFiles: function() {
			this.cleanScreen();

			// Create the loop view
			var file_list = new buddydrive.Views.Main( { collection: this.items } );

			this.views.add( { id: 'files', view: file_list } );

			file_list.inject( '#buddydrive-main' );
		},

		editFile: function( id ) {
			this.cleanScreen();

			var file_edit = new buddydrive.Views.EditForm( { model: this.items.get( id ), item_id: id, collection: this.items } );

			this.views.add( { id: 'edit', view: file_edit } );

			file_edit.inject( '#buddydrive-main' );
		},

		cleanScreen: function() {
			if ( ! _.isUndefined( this.views.models ) ) {
				_.each( this.views.models, function( model ) {
					model.get( 'view' ).remove();
				}, this );

				this.views.reset();
			}
		}
	};

	buddydrive.App.Router = Backbone.Router.extend( {
		routes: {
			'edit/:id': 'editItem',
			'view/:id': 'viewItem',
			'user/:id': 'userFilter',
			'' : 'listView'
		},

		editItem: function( item_id ) {
			if ( ! item_id ) {
				return;
			}

			buddydrive.App.editFile( item_id );
		},

		viewItem: function( folder_id ) {
			if ( ! folder_id ) {
				return;
			}

			buddydrive.App.Query.set( {
				'buddydrive_parent': folder_id,
				paged: 1
			}, { silent: true } );

			buddydrive.App.listFiles();
		},

		userFilter: function ( user_id ) {
			if ( ! user_id ) {
				return;
			}

			buddydrive.App.Query.set( {
				'user_id': user_id,
				paged: 1
			}, { silent: true } );

			buddydrive.App.listFiles();
		},

		listView: function() {
			var persistentArgs = _.omit( buddydrive.App.Query.attributes, ['buddydrive_parent', 'user_id'] );

			buddydrive.App.Query.clear( { silent: true } );
			buddydrive.App.Query.set( _.extend( persistentArgs, { paged: 1} ), { silent: true } );

			buddydrive.App.listFiles();
		}
	} );

	buddydrive.App.start();

} )( buddydrive, jQuery );

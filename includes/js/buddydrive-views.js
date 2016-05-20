/* globals buddydrive, _, Backbone, BP_Uploader */
window.buddydrive = window.buddydrive || {};

( function( exports, $ ) {

	// Init Views
	buddydrive.Views = buddydrive.Views || {};

	// Extend Backbone.View with .prepare(), .inject() and cleanViews
	buddydrive.View = buddydrive.Backbone.View.extend( {
		inject: function( selector ) {
			this.render();
			$(selector).html( this.el );
			this.views.ready();
		},

		prepare: function() {
			if ( ! _.isUndefined( this.model ) && _.isFunction( this.model.toJSON ) ) {
				return this.model.toJSON();
			} else {
				return {};
			}
		},

		cleanViews: function( selector ) {
			_.each( this.views._views[ selector ], function( view ) {
				view.remove();
			} );
		}
	} );

	buddydrive.Views.Feedback = buddydrive.View.extend( {
		tagName: 'div',
		className: 'buddydrive-feedback',

		initialize: function() {
			this.value = this.options.value;
			this.type  = 'info';

			if ( ! _.isUndefined( this.options.type ) && 'info' !== this.options.type ) {
				this.type = this.options.type;
			}
		},

		render: function() {
			this.$el.html( $( '<p></p>' ).html( this.value ).addClass( this.type ) );
			return this;
		}
	} );

	buddydrive.Views.Main = buddydrive.View.extend( {
		tagName   : 'div',

		initialize: function() {
			this._views = {};

			// Init the Breadcrumb
			this.breadCrumbs = new Backbone.Collection();

			// Init the actions
			this.actions = new Backbone.Collection();

			// Init the warnings
			this.warnings = new Backbone.Model();

			this.views.set( [
				new buddydrive.View( { tagName: 'ul', className: 'subsubsub' } ),
				new buddydrive.Views.Toolbar( { actions: this.actions }),
				new buddydrive.View( { tagName: 'div', id: 'buddydrive-uploader', className: 'buddydrive-hide' } ),
				new buddydrive.View( { tagName: 'div', id: 'buddydrive-actions-form', className: 'buddydrive-hide' } ),
				new buddydrive.View( { tagName: 'div', id: 'buddydrive-status' } ),
				new buddydrive.View( { tagName: 'ul',  id: 'buddydrive-browser' } ),
				new buddydrive.View( { tagName: 'div', id: 'buddydrive-pagination' } )
			] );

			// Request for files
			this.requestFiles();

			// Update the File views
			this.collection.on( 'add', this.injectFile, this );
			this.collection.on( 'reset', this.cleanFiles, this );
			this.collection.on( 'remove', this.removeFileView, this );

			// Update the BreadCrumbs
			this.breadCrumbs.on( 'add', this.injectCrumb, this );
			this.breadCrumbs.on( 'reset', this.cleanCrumbs, this );

			// Update the action forms
			this.actions.on( 'change:active', this.doToolbarActions, this );

			// Make sure to stop listening to changes in removed views
			this.listenTo( buddydrive.App.Query, 'change', this.requestFiles );

			// Listen to uploads
			if ( ! _.isUndefined( buddydrive.Uploader ) ) {
				this.views.add( '#buddydrive-uploader', new buddydrive.Views.UploadTool( { actions: this.actions, warnings: this.warnings } ) );

				this.listenTo( buddydrive.Uploader.filesQueue, 'add', this.insertFiles );
				this.listenTo( buddydrive.Uploader.filesError, 'add', this.displayErrors );

				buddydrive.App.UploadParams = _.extend(
					_.pick( BP_Uploader.settings.defaults.multipart_params.bp_params, ['item_id', 'privacy' ] ),
					{ 'parent_folder_id': 0 }
				);
			}

			// Listen to Warnings
			this.listenTo( this.warnings, 'change:message', this.displayErrors );
		},

		displayErrors: function( file ) {
			if ( ! file.get( 'message' ) ) {
				this.cleanViews( '#buddydrive-status' );
				return;
			}

			this.views.add( '#buddydrive-status', new buddydrive.Views.Feedback( {
				type: 'error',
				value: file.get( 'message' )
			} ) );
		},

		insertFiles: function( file ) {
			this.collection.add( file );
		},

		removeFileView:function( file ) {
			_.each( this.views._views['#buddydrive-browser'], function( view ) {
				if ( view.model.get( 'id' ) === file.get( 'id' ) ) {
					view.remove();
				}
			} );
		},

		requestFiles: function( model ) {
			var isPagination = false;

			if ( ! _.isUndefined( model ) ) {
				isPagination = model.hasChanged( 'paged' ) && 1 !== model.get( 'paged' );
			}

			if ( ! isPagination ) {
				this.collection.reset();
			}

			this.views.set( '#buddydrive-pagination', new buddydrive.Views.Loader() );

			// Remove all warnigs
			this.cleanViews( '#buddydrive-status' );

			this.collection.fetch( {
				data    : buddydrive.App.Query.attributes,
				remove  : ! isPagination,
				success : _.bind( this.filesFetched, this ),
				error   : _.bind( this.filesFetchError, this )
			} );
		},

		filesFetched: function( collection, response ) {
			this.breadCrumbs.reset();

			// Use buddydrive.Settings to get "All" in Admin / The Group or the User Avatar
			var crumbs = [ { id: 'all', 'text': buddydrive.Strings.allCrumb, 'current': true } ],
			    first =  _.first( response.items ) || {};

			if ( buddydrive.App.Query.get( 'buddydrive_parent' ) || buddydrive.App.Query.get( 'user_id' ) ) {
				_.first( crumbs ).current = false;
			}

			if ( buddydrive.App.Query.get( 'user_id' ) ) {
				if ( ! _.isUndefined( first.user_avatar ) ) {
					crumbs.push( {
						id: 'user-' + buddydrive.App.Query.get( 'user_id' ),
						text: first.user_avatar,
						current: ! buddydrive.App.Query.get( 'buddydrive_parent' )
					} );
				}
			}

			if ( buddydrive.App.Query.get( 'buddydrive_parent' ) ) {
				if ( _.isUndefined( first.post_parent_title ) ) {
					first.post_parent_title = collection.options.post_parent_title;
				}

				crumbs.push( {
					id: 'folder-' + buddydrive.App.Query.get( 'buddydrive_parent' ),
					text: first.post_parent_title,
					current: true
				} );
			}

			this.breadCrumbs.add( crumbs );

			if ( collection.options.has_more_items ) {
				this.views.set( '#buddydrive-pagination', new buddydrive.Views.LoadMore( { model: buddydrive.App.Query, text: buddydrive.Strings.loadMore } ) );
			} else {
				this.cleanViews( '#buddydrive-pagination' );
			}

			if ( ! _.isUndefined( collection.options.no_items_found ) ) {
				this.views.set( '#buddydrive-status', new buddydrive.Views.Feedback( {
					value: collection.options.no_items_found
				} ) );
			}
		},

		cleanFiles: function() {
			this.cleanViews( '#buddydrive-browser' );
		},

		cleanCrumbs: function () {
			this.cleanViews( '.subsubsub' );
		},

		filesFetchError: function( collection, response ) {
			if ( ! response.message ) {
				return;
			}

			this.views.set( '#buddydrive-status', new buddydrive.Views.Feedback( {
				type: 'error',
				value: response.message
			} ) );

			// Remove the loader
			this.cleanViews( '#buddydrive-pagination' );
		},

		injectFile: function( file ) {
			var options = {}, position;

			// If the file is uploading, prepend it.
			if ( file.get( 'uploading' ) || file.get( 'uploaded' ) || file.get( 'new_folder' ) ) {
				options = { at: 0 };
			}

			// Try to find the best place to inject the uploaded file
			if ( file.get( 'file_id' ) && ! _.isUndefined( buddydrive.Uploader ) ) {
				position = _.indexOf( _.pluck( buddydrive.Uploader.filesQueue.models, 'id' ), file.get( 'file_id' ) );
				if ( -1 !== position ) {
					options = { at: position };
				}
			}

			// Pfiou!
			if ( this.collection.length > 0 && file.get( 'new_folder' ) && 'title' === buddydrive.App.Query.get( 'orderby') ) {
				position = -1;
				_.each(
					this.collection.pluck( 'title' ).sort( function( a, b ){
					    return a.localeCompare( b );
					} ), function( title, index ) {
						if ( 0 > title.localeCompare( file.get( 'title') ) ) {
							position = index + 1;
						}
				} );

				if ( -1 !== position ) {
					options = { at: position };
				}
			}

			this.views.add( '#buddydrive-browser', new buddydrive.Views.File( { model: file } ), options );
		},

		injectCrumb: function( crumb ) {
			this.views.add( '.subsubsub', new buddydrive.Views.BreadCrumb( { model: crumb } ) );
		},

		doToolbarActions: function( action ) {
			// Remove all warnigs
			this.cleanViews( '#buddydrive-status' );

			if ( ! action.get( 'active' ) ) {
				if ( 'new_file' === action.get( 'id' ) ) {
					$( '#buddydrive-uploader' ).addClass( 'buddydrive-hide' );
				} else {
					$( '#buddydrive-actions-form' ).addClass( 'buddydrive-hide' );

					if ( 'bulk' === action.get( 'id' ) ) {
						$( '#buddydrive-browser' ).removeClass( 'bulk-select' );
					}
				}
				return;
			}

			// Remove all open sharing dialog
			$( '.buddydrive-share-dialog' ).addClass( 'buddydrive-hide' );

			_.each( this.actions.models, function( model ) {
				if ( action.get( 'id' ) === model.get( 'id' ) ) {
					if ( 'new_file' === action.get( 'id' ) ) {
						$( '#buddydrive-uploader' ).removeClass( 'buddydrive-hide' );
						$( '#buddydrive-actions-form' ).addClass( 'buddydrive-hide' );
						$( '#buddydrive-browser').removeClass( 'bulk-select' );

						if ( buddydrive.App.Query.get( 'buddydrive_parent' ) && ! _.isUndefined( this.collection.options.post_parent_infos ) ) {
							this.views.set( '#buddydrive-status', new buddydrive.Views.Feedback( {
								value: this.collection.options.post_parent_infos
							} ) );
						}

						// Make sure clicking on the browse button will open the browse dialog
						$( '#bp-upload-ui.drag-drop' ).find( '.moxie-shim-html5' ).css( {
							'height' : $( '#bp-browse-button' ).outerHeight() + 5 + 'px',
							'width'  : $( '#bp-browse-button' ).outerWidth() + 5 + 'px',
							'top'    : $( '#bp-browse-button' ).position().top + 'px',
							'left'   : $( '#bp-browse-button' ).position().left + 'px'
						} );
					} else {
						$( '#buddydrive-uploader' ).addClass( 'buddydrive-hide' );
						$( '#buddydrive-actions-form' ).removeClass( 'buddydrive-hide' );
						$( '#buddydrive-browser' ).removeClass( 'bulk-select' );

						if ( 'bulk' === action.get( 'id' ) ) {
							$( '#buddydrive-browser' ).addClass( 'bulk-select' );
							this.views.set( '#buddydrive-actions-form', new buddydrive.Views.bulkEdit( {
								collection: this.collection,
								bulkActions: buddydrive.Settings.bulk_actions,
								warnings: this.warnings
							} ) );
						} else if ( 'new_folder' === action.get( 'id' ) ) {
							this.views.set( '#buddydrive-actions-form', new buddydrive.Views.newFolder( { model: model, warnings: this.warnings } ) );
						} else if ( 'stats' === action.get( 'id' ) ) {
							this.views.set( '#buddydrive-actions-form', new buddydrive.Views.userStats() );
						}
					}
				} else {
					model.set( 'active', false, { silent: true } );
				}

			}, this );
		}
	} );

	buddydrive.Views.Loader = buddydrive.View.extend( {
		tagName: 'span',
		id: 'buddydrive-loading'
	} );

	buddydrive.Views.File = buddydrive.View.extend( {
		tagName   : 'li',
		template  : buddydrive.template( 'buddydrive-file' ),
		className : 'buddydrive-item',

		events: {
			'click'                                : 'bulkSelect',
			'click .buddydrive-owner'              : 'filterForUser',
			'click .buddydrive-share'              : 'showSharingBox',
			'click .buddydrive-share-dialog-close' : 'hideSharingBox',
			'click .buddydrive-share-url'          : 'selectEmbed'
		},

		initialize: function() {
			this.listenTo( this.model, 'change:percent', this.showProgress );
			this.listenTo( this.model, 'change:id', this.deleteEntry );
			this.listenTo( this.model, 'change:url', this.updateEntry );

			if ( ! this.model.get( 'can_bulk_edit' ) ) {
				this.el.className += ' not-bulk-editable';
			}
		},

		deleteEntry: function( model ) {
			if ( _.isUndefined( model.get( 'id' ) ) ) {
				this.views.view.remove();

				// Remove silently the model from the collection
				buddydrive.App.items.remove( model, { silent: true } );
			}
		},

		showProgress: function( model ) {
			if ( ! _.isUndefined( model.get( 'percent' ) ) ) {
				$( '#' + model.get( 'id' ) + ' .buddydrive-progress .buddydrive-bar' ).css( 'width', model.get( 'percent' ) + '%' );
			}
		},

		updateEntry: function( model ) {
			var item = _.extend(
				{ id: model.get( 'buddydrive_id' ), file_id: model.get( 'id' ) },
				_.omit( model.attributes, ['id', 'date', 'filename', 'uploading', 'buddydrive_id', 'url' ] )
			);

			// Remove the uploading model from the main collection
			buddydrive.App.items.remove( model );

			// Add the uploaded model to the main collection
			buddydrive.App.items.add( item );
		},

		bulkSelect: function( event ) {
			if ( ! $( '#buddydrive-browser').hasClass( 'bulk-select' ) ) {
				return event;
			}

			event.preventDefault();

			if ( ! this.model.get( 'bulk_selected' ) ) {
				this.model.set( 'bulk_selected', true );
				this.$el.addClass( 'bulk-selected' );
			} else {
				this.model.set( 'bulk_selected', false );
				this.$el.removeClass( 'bulk-selected' );
			}
		},

		filterForUser: function( event ) {
			if ( ! $( 'body').hasClass( 'wp-admin' ) || $( '#buddydrive-browser').hasClass( 'bulk-select' ) ) {
				return event;
			}

			event.preventDefault();

			buddydrive.App.Query.set( {
				'buddydrive_parent': 0,
				paged: 1
			}, { silent: true } );

			buddydrive.App.router.navigate( 'user/' + $(event.currentTarget).data( 'user-id' ), { trigger: true } );
		},

		showSharingBox: function( event ) {
			if ( $( '#buddydrive-browser').hasClass( 'bulk-select' ) ) {
				return event;
			}

			event.preventDefault();

			this.$el.find( '.buddydrive-share-dialog' ).removeClass( 'buddydrive-hide' );
		},

		hideSharingBox: function( event ) {
			event.preventDefault();

			this.$el.find( '.buddydrive-share-dialog' ).addClass( 'buddydrive-hide' );
		},

		selectEmbed: function( event ) {
			event.preventDefault();

			event.target.select();
		}
	} );

	buddydrive.Views.BreadCrumb = buddydrive.View.extend( {
		tagName: 'li',
		template  : buddydrive.template( 'buddydrive-nav-item' ),

		events: {
			'click .buddydrive-crumb' : 'allQuery'
		},

		initialize: function() {
			if ( true === this.model.get( 'current' ) ) {
				this.el.className = 'current';
			}
		},

		allQuery: function( event ) {
			event.preventDefault();

			var route = $( event.currentTarget ).data( 'crumb' );

			if ( 'all' === route || ! route ) {
				buddydrive.App.router.navigate( '#', { trigger: true } );

			// This must be a user
			} else {
				buddydrive.App.Query.set( {
					'buddydrive_parent': 0,
					paged: 1
				}, { silent: true } );

				buddydrive.App.router.navigate( '#user/' + route.replace( 'user-', '' ), { trigger: true } );
			}
		}
	} );

	buddydrive.Views.manageBarItem = buddydrive.View.extend( {
		tagName: 'li',
		template:  buddydrive.template( 'buddydrive-manage-toolbar' ),

		events: {
			'click' : 'doAction'
		},

		doAction: function( event ) {
			event.preventDefault();

			if ( ! this.model.get( 'active' ) ) {
				this.model.set( 'active', true );
			} else {
				this.model.set( 'active', false );
			}
		}
	} );

	buddydrive.Views.manageBar = buddydrive.View.extend( {
		tagName: 'ul',
		id: 'buddydrive-manage-actions',

		initialize: function() {
			var isFolder       = buddydrive.App.Query.get( 'buddydrive_parent' ),
			    isMembersScope = buddydrive.Settings.buddydrive_scope === 'members';

			_.each( buddydrive.Settings.manage_toolbar, function( action ) {
				// Do not display the Add folder button if browsing a folder
				if ( ! isFolder && ! isMembersScope || isFolder && action.id !== 'new_folder' || action.id === 'stats' ) {
					this.collection.add( action );
					this.addItemBar( this.collection.get( action.id ) );
				}
			}, this );
		},

		addItemBar: function( action ) {
			this.views.add( new buddydrive.Views.manageBarItem( { model: action } ) );
		}
	} );

	buddydrive.Views.Toolbar = buddydrive.View.extend( {
		tagName: 'nav',
		className: 'buddydrive-toolbar',

		initialize: function() {
			var subViews = [];
			this._views  = {};

			if ( ! _.isUndefined( buddydrive.Uploader ) ) {
				subViews.push( new buddydrive.Views.manageBar( { collection: this.options.actions } ) );
			}

			this.primary   = new buddydrive.Views.LoopFilter( {
				filters: buddydrive.Settings.loop_filters,
				current: buddydrive.App.Query.get( 'orderby' ) || 'modified'
			} );

			this.secondary = new buddydrive.Views.SearchForm( { model: buddydrive.App.Query } );

			this.views.set( _.union( subViews, [ this.primary, this.secondary ] ) );

			this.primary.model.on( 'change', this.orderQuery, this );
		},

		orderQuery: function( model ) {
			buddydrive.App.Query.set( { 'orderby': model.get( 'selected' ), 'paged': 1 } );
		}
	} );

	buddydrive.Views.Input = buddydrive.View.extend( {
		tagName  : 'input',

		initialize: function() {
			if ( ! _.isObject( this.options ) ) {
				return;
			}

			_.each( this.options, function( value, key ) {
				if ( 'value' === key ) {
					this.$el.val( value );
				} else {
					this.$el.prop( key, value );
				}
			}, this );
		}
	} );

	buddydrive.Views.Label = buddydrive.Views.Input.extend( {
		tagName  : 'label',

		render: function() {
			this.$el.html( this.options.text );
			return this;
		}
	} );

	buddydrive.Views.Button = buddydrive.Views.Label.extend( {
		tagName  : 'button'
	} );

	buddydrive.Views.LoadMore = buddydrive.Views.Button.extend( {
		id: 'buddydrive-load-more',

		events: {
			'click': 'loadMore'
		},

		loadMore: function( event ) {
			event.preventDefault();

			this.model.set( 'paged', buddydrive.App.items.options.paged + 1 );
		}
	} );

	buddydrive.Views.SearchForm = buddydrive.Views.Input.extend( {
		tagName:   'input',
		className: 'search',
		id:        'buddydrive-search-input',

		attributes: {
			type:        'search',
			placeholder: 'Search'
		},

		events: {
			'keyup':  'maybeSearch',
			'search': 'search'
		},

		render: function() {
			this.el.value = this.model.escape( 'search' );
			return this;
		},

		search: function( event ) {
			if ( event.target.value ) {
				this.model.set( 'search', event.target.value );
			} else {
				this.model.unset('search');
			}
		},

		maybeSearch: function( event ) {
			// Webkit browsers are supporting the search event
			if ( true === /WebKit/.test( navigator.userAgent ) ) {
				event.preventDefault();
				return;
			}

			// Launch search when the return key is hit for all other browsers
			if ( 13 === event.keyCode ) {
				this.search( event );
			}
		}
	} );

	buddydrive.Views.PrivacyFilter = buddydrive.View.extend( {
		tagName:   'select',
		id:        'buddydrive-privacy-filter',

		attributes: {
			name         : 'privacy',
			'aria-label' : buddydrive.Strings.privacyFilterLabel
		},

		events: {
			change: 'change'
		},

		keys: [],

		initialize: function() {
			var current = this.options.current;
			this.model  = new Backbone.Model();

			this.filters = this.options.filters || {};

			// Build `<option>` elements.
			this.$el.html( _.chain( this.filters ).map( function( filter, value ) {
				return {
					el: current === value ? $( '<option></option>' ).val( value ).html( filter.text ).prop( 'selected', true )[0] : $( '<option></option>' ).val( value ).html( filter.text )[0],
					priority: filter.priority || 5
				};
			}, this ).sortBy( 'priority' ).pluck( 'el' ).value() );
		},

		change: function() {
			var filter = this.filters[ this.el.value ];
			if ( filter ) {
				this.model.set( { 'selected': this.el.value } );
			}
		}
	} );

	buddydrive.Views.bulkEdit = buddydrive.View.extend( {
		tagName:   'div',
		id:        'buddydrive-bulk-edit',

		events: {
			'click .bulk-action': 'doBulkEdit'
		},

		initialize: function() {
			this.bulkActions  = this.options.bulkActions || {};

			if ( this.options.warnings.get( 'message' ) ) {
				this.options.warnings.clear();
			}

			// Build `<button>` elements.
			this.$el.html( _.chain( this.bulkActions ).map( function( action ) {
				var classes = 'button-primary button-large bulk-action';

				if ( 'remove' === action.id && ! buddydrive.App.Query.get( 'buddydrive_parent' ) ) {
					classes += ' buddydrive-hide';
				}

				return $( '<button></button>' ).html( action.text ).prop( 'class', classes ).attr( 'data-action', action.id )[0];
			}, this ).value() );
		},

		doBulkEdit: function( event ) {
			var bulkSelected = [];

			event.preventDefault();

			_.each( this.collection.models, function( item ) {
				if ( true === item.get( 'bulk_selected' ) ) {
					bulkSelected.push( item.get( 'id' ) );
				}
			} );

			if ( ! bulkSelected.length ) {
				return;
			}

			this.collection.bulkEdit( {
				data: {
					type: $( event.currentTarget ).data( 'action' ),
					items: bulkSelected
				},
				error: _.bind( this.bulkError, this )
			} );
		},

		bulkError: function( response ) {
			if ( ! response.message ) {
				return;
			}

			this.options.warnings.set( 'message', response.message );
		}
	} );

	buddydrive.Views.userStats = buddydrive.View.extend( {
		tagName: 'div',
		id: 'buddydrive-user-stats',

		template:  buddydrive.template( 'buddydrive-stats' ),

		initialize: function() {
			this.model = new buddydrive.Models.Stat();
			this.model.stat();

			this.listenTo( this.model, 'change:used', this.rerender );
		},

		rerender: function() {
			this.render();
		}
	} );

	buddydrive.Views.LoopFilter = buddydrive.Views.PrivacyFilter.extend( {
		id: 'buddydrive-filter',
		attributes: {
			name         : 'buddydrive_filter',
			'aria-label' : buddydrive.Strings.loopFilterLabel
		}
	} );

	buddydrive.Views.Item = buddydrive.View.extend( {
		tagName:   'li',
		className: 'buddydrive-item',
		template:  buddydrive.template( 'buddydrive-object' ),

		attributes: {
			role: 'checkbox'
		},

		initialize: function() {
			if ( this.model.get( 'selected' ) ) {
				this.el.className += ' selected';
			}
		},

		events: {
			'click' : 'setObject',
			'click .buddydrive-object-remove' : 'removeView'
		},

		setObject:function( event ) {
			event.preventDefault();

			if ( $( event.target ).hasClass( 'buddydrive-object-remove' ) ) {
				return;
			}

			if ( true === this.model.get( 'selected' ) ) {
				this.model.set( 'selected', false );
			} else {
				this.model.set( 'selected', true );
			}
		},

		removeView: function( event ) {
			event.preventDefault();

			this.model.set( 'selected', false );
			this.views.view.remove();
		}
	} );

	buddydrive.Views.ObjectSelection = buddydrive.View.extend( {
		tagName: 'ul',
		id     : 'buddydrive-object-selection',

		initialize: function() {
			this.collection.on( 'add', this.addItemView, this );
			this.collection.on( 'reset', this.cleanView, this );

			this.listenTo( this.collection, 'change:selected', this.updateSelection );
		},

		addItemView: function( item ) {
			var parent = this.views.parent.model.get( 'type' ) || '';

			this.views.add( new buddydrive.Views.Item( { model: item } ) );

			// Make sure it's not possible to edit a folder to be in multiple groups
			if ( 'group' === item.get( 'buddydrive_type' ) && item.get( 'selected' ) && 'folder' === parent && ! $( '#buddydrive-lookfor' ).hasClass( 'buddydrive-hide' ) ) {
				$( '#buddydrive-lookfor' ).addClass( 'buddydrive-hide' );
			}
		},

		cleanView: function() {
			_.each( this.views._views[''], function( view ) {
				view.remove();
			} );
		},

		updateSelection: function( item ) {
			var parent = this.views.parent.model.get( 'type' ) || '';

			if ( false === item.get( 'selected' ) ) {
				this.collection.remove( item );
			}

			// Make sure it's possible to edit a folder to be in a group
			if ( 'group' === item.get( 'buddydrive_type' ) && 'folder' === parent && $( '#buddydrive-lookfor' ).hasClass( 'buddydrive-hide' ) ) {
				$( '#buddydrive-lookfor' ).removeClass( 'buddydrive-hide' );
			}
		}
	} );

	buddydrive.Views.AutoComplete = buddydrive.Views.ObjectSelection.extend( {
		tagName  : 'ul',
		id       : 'buddydrive-autocomplete',

		events: {
			'keyup':  'autoComplete'
		},

		initialize: function() {
			var autocomplete = new buddydrive.Views.Input( {
				type: 'text',
				id: 'buddydrive-lookfor',
				placeholder: this.options.placeholder || ''
			} ).render();

			this.$el.prepend( $( '<li></li>' ).html( autocomplete.$el ) );

			this.collection.on( 'add', this.maybeAddItemView, this );
			this.collection.on( 'reset', this.cleanView, this );

			this.listenTo( this.collection, 'change:selected', this.updateSelection );
		},

		maybeAddItemView: function( model ) {
			if ( ! this.options.selection.get( model.get( 'id' ) ) ) {
				this.addItemView( model );
			}
		},

		autoComplete: function() {
			var lookfor = $( '#buddydrive-lookfor' ).val();

			// Reset the collection before starting a new search
			this.collection.reset();

			if ( 2 > lookfor.length ) {
				return;
			}

			this.collection.fetch( {
				data: _.extend(
					_.pick( buddydrive.Settings, 'buddydrive_scope' ),
					{
						buddydrive_type : this.options.buddydrive_type,
						user_id         : this.options.user_id,
						search          : lookfor
					}
				),
				success : _.bind( this.itemFetched, this ),
				error : _.bind( this.itemFetched, this )
			} );
		},

		itemFetched: function( items ) {
			if ( ! items.length ) {
				this.cleanView();
			}
		},

		updateSelection: function( model ) {
			var allow_multiple = this.options.allow_multiple || false;

			/**
			 * As it is possible for any members of a group to add file to a group's folder,
			 * Allowing folders to be shared in multiple groups can be problematic
			 * if one member of a one of the groups is not a member of the other groups..
			 * So we simply do not allow multiple groups for folders.
			 */
			if ( true === this.options.is_folder && 'groups' === this.options.buddydrive_type ) {
				allow_multiple = false;
			}

			if ( true === model.get( 'selected' ) ) {
				this.options.selection.add( model );
				$( '#buddydrive-lookfor' ).val( '' );
				this.cleanView();

				// Hide only if one object at a time !
				if ( ! allow_multiple ) {
					this.$el.addClass( 'buddydrive-hide' );
				}
			} else {
				if ( ! allow_multiple ) {
					this.$el.removeClass( 'buddydrive-hide' );
				}
			}
		}
	} );

	buddydrive.Views.EditForm = buddydrive.View.extend( {
		tagName   : 'section',
		className : 'buddydrive-item',

		initialize: function() {
			// Init the warnings
			this.warnings = new Backbone.Model();

			// Listen to Warnings
			this.listenTo( this.warnings, 'change:message', this.displayErrors );

			this.views.add( new buddydrive.View( { tagName: 'header', id: 'buddydrive-item-header' } ) );
			this.views.add( new buddydrive.View( { tagName: 'article', id: 'buddydrive-item-content' } ) );
			this.views.add( new buddydrive.View( { tagName: 'div', id: 'buddydrive-status' } ) );

			if ( _.isUndefined( this.model ) ) {
				if ( this.options.item_id ) {
					this.collection.fetch( {
						data    : _.extend( _.pick( buddydrive.Settings, 'buddydrive_scope' ), { id: this.options.item_id, 'is_edit': true } ),
						success : _.bind( this.itemFetched, this ),
						error   : _.bind( this.itemError, this )
					} );

					this.collection.on( 'add', this.displayDetails, this );
				}
			} else {
				this.displayDetails( this.model );
			}
		},

		itemFetched: function( collection, response ) {
			if ( ! collection.length ) {
				$( 'header#buddydrive-item-header' ).addClass( 'buddydrive-hide' );
				$( 'article#buddydrive-item-content' ).addClass( 'buddydrive-hide' );

				if ( ! _.isUndefined( response.metas.no_items_found ) ) {
					this.warnings.set( 'message', response.metas.no_items_found );
				}
			} else {
				$( 'header#buddydrive-item-header' ).removeClass( 'buddydrive-hide' );
				$( 'article#buddydrive-item-content' ).removeClass( 'buddydrive-hide' );
			}
		},

		itemError: function( collection, response ) {
			if ( response.message ) {
				$( 'header#buddydrive-item-header' ).addClass( 'buddydrive-hide' );
				$( 'article#buddydrive-item-content' ).addClass( 'buddydrive-hide' );

				this.warnings.set( 'message', response.message );
			}
		},

		displayErrors: function( model ) {
			// Remove previous warnings
			this.cleanViews( '#buddydrive-status' );

			this.views.add( '#buddydrive-status', new buddydrive.Views.Feedback( {
				type:  model.get( 'type' ) || 'error',
				value: model.get( 'message' )
			} ) );
		},

		displayDetails: function( file ) {
			this.views.add( '#buddydrive-item-header', new buddydrive.Views.editHeader( { model: file } ) );
			this.views.add( '#buddydrive-item-content', new buddydrive.Views.Edit( { model: file, warnings: this.warnings } ) );
		}
	} );

	buddydrive.Views.editHeader = buddydrive.View.extend( {
		tagName   : 'div',
		template  : buddydrive.template( 'buddydrive-edit-header' )
	} );

	buddydrive.Views.Edit = buddydrive.View.extend( {
		tagName   : 'form',
		template  : buddydrive.template( 'buddydrive-edit-details' ),
		className : 'buddydrive-item-details',

		events: {
			'reset'                           : 'resetItem',
			'submit'                          : 'updateItem',
			'click #buddydrive-remove-parent' : 'removeParentFolder'
		},

		initialize: function() {
			// Clone the model to eventually reset it if needed
			this.resetModel = this.model.clone();

			// Init the BP Objects
			this.bpobjects = new buddydrive.Collections.bpObjects();

			// Init Privacy filters
			this.privacyFilters = buddydrive.Settings.privacy_filters;

			if ( 'folder' === this.model.get( 'type' ) ) {
				this.privacyFilters = _.omit( this.privacyFilters, 'folder' );
			}

			if ( ! this.model.get( 'post_parent' ) ) {
				this.privacyDetails();
			}
		},

		privacyDetails: function() {
			var privacy = this.model.get( 'privacy' );
			var select = new buddydrive.Views.PrivacyFilter( { filters: this.privacyFilters, current: privacy } );

			this.views.add( '#buddydrive-privacy-edit', new buddydrive.Views.Label( { 'for': 'buddydrive-privacy-filter', 'text' : buddydrive.Strings.privacyFilterLabel } ) );
			this.views.add( '#buddydrive-privacy-edit', select );

			if ( 'password' === privacy ) {
				this.passwordFrame();
			} else if ( 'groups' === privacy || 'folder' === privacy || 'members' === privacy ) {
				this.objectFrame( privacy );
			}

			select.model.on( 'change', this.updatePrivacyOptions, this );
		},

		passwordFrame: function() {
			this.views.add( '#buddydrive-privacy-edit', new buddydrive.Views.Label( {
				'for'   : 'buddydrive-edit-password',
				'text'  : buddydrive.Strings.passwordInputLabel
			} ) );

			this.views.add( '#buddydrive-privacy-edit', new buddydrive.Views.Input( {
				type  : 'text',
				id    : 'buddydrive-edit-password',
				name  : 'password',
				value : this.model.get( 'password' )
			} ) );
		},

		objectFrame: function( privacy ) {
			var allow_multiple = false, placeholder = '', prefetch = {};

			// Reset collections
			this.bpobjects.reset();
			this.selection = this.bpobjects.clone();

			if ( ! _.isUndefined( this.privacyFilters[ privacy ] ) ) {
				allow_multiple = this.privacyFilters[privacy].allow_multiple;
				placeholder    = this.privacyFilters[privacy].autocomplete_placeholder;
			}

			this.views.add( '#buddydrive-privacy-edit', new buddydrive.Views.AutoComplete( {
				collection      : this.bpobjects,
				user_id         : this.model.get( 'user_id' ),
				buddydrive_type : privacy,
				allow_multiple  : allow_multiple,
				placeholder     : placeholder,
				selection       : this.selection,
				is_folder       : 'folder' === this.model.get( 'type' )
			} ) );

			this.views.add( '#buddydrive-privacy-edit', new buddydrive.Views.ObjectSelection( { collection: this.selection } ) );

			// Populate with existing
			if ( 'groups' === privacy && this.model.get( 'group' ) ) {
				prefetch = {
					'user_id'         : this.model.get( 'user_id' ),
					'include'         : this.model.get( 'group' ),
					'buddydrive_type' : 'groups',
					'existing'        : true
				};
			}

			if ( 'members' === privacy && this.model.get( 'members' ) ) {
				prefetch = {
					'include'         : this.model.get( 'members' ),
					'buddydrive_type' : 'members',
					'existing'        : true
				};
			}

			if ( true === prefetch.existing ) {
				this.selection.fetch( {
					data : _.extend(
						_.pick( buddydrive.Settings, 'buddydrive_scope' ),
						prefetch
					)
				} );
			}
		},

		resetItem: function( event ) {
			event.preventDefault();

			if ( true === this.model.get( 'saving' ) ) {
				return;
			}

			Backbone.history.history.back();
		},

		updateItem: function( event ) {
			event.preventDefault();

			if ( true === this.model.get( 'saving' ) ) {
				return;
			}

			var postArray = _.pick( this.model.attributes, ['id', 'user_id', 'title', 'content', 'privacy'] ),
			    meta = {}, submitError = [];

			// Set the content and meta
			_.each( this.$el.serializeArray(), function( pair ) {
				pair.name = pair.name.replace( '[]', '' );

				if ( -1 !== _.indexOf( ['title', 'content', 'privacy', 'buddydrive_object', 'password'], pair.name ) ) {
					if ( 'buddydrive_object' === pair.name ) {
						if ( ! _.isArray( postArray[ postArray.privacy ] ) ) {
							postArray[ postArray.privacy ] = [ pair.value ];
						} else {
							postArray[ postArray.privacy ].push( pair.value );
						}
					} else {
						postArray[ pair.name ] = pair.value;
					}
				} else if ( -1 === _.indexOf( ['id', 'user_id'], pair.name ) ) {
					if ( _.isUndefined( meta[ pair.name ] ) ) {
						meta[ pair.name ] = pair.value;
					} else {
						if ( ! _.isArray( meta[ pair.name ] ) ) {
							meta[ pair.name ] = [ meta[ pair.name ] ];
						}

						meta[ pair.name ].push( pair.value );
					}
				}
			} );

			// Validate file or folder
			if ( ! postArray.title ) {
				submitError.push( 'title' );
			}

			if ( -1 !== _.indexOf( ['folder', 'groups', 'members', 'password'], postArray.privacy ) &&
				( _.isUndefined( postArray[ postArray.privacy ] ) || ! postArray[ postArray.privacy ].length ) ) {
				submitError.push( postArray.privacy );
			}

			if ( submitError.length !== 0 ) {
				this.options.warnings.set( 'message', buddydrive.Strings.editErrors[ _.first( submitError ) ] );
				return;

			// Inform the user we are about to save changes
			} else {
				this.options.warnings.set( {
					type   : 'info',
					message: buddydrive.Strings.saveEdits
				} );
			}

			// Reset the parent if needed
			if ( this.model.get( 'post_parent' ) && 'folder' !== postArray.privacy ) {
				postArray.parent_folder_id = 0;
				this.model.set( 'post_parent', 0, { silent: true } );
			}

			// Set the post_parent to redirect inside the folder once saved
			if ( 'folder' === postArray.privacy && _.isArray( postArray.folder ) ) {
				this.model.set( 'post_parent', _.first( postArray.folder ), { silent: true } );
			}

			this.model.set( 'saving', true );

			this.model.update( _.extend( postArray, meta ), {
				success : _.bind( this.itemUpdated, this ),
				error   : _.bind( this.itemUpdateError, this )
			} );
		},

		itemUpdated: function() {
			var redirect = '#';

			this.model.set( 'saving', false );

			if ( this.model.get( 'post_parent' ) ) {
				redirect = '#view/' + this.model.get( 'post_parent' );
			}

			buddydrive.App.Query.set( { orderby: 'modified', search: '' }, { silent: true } );
			buddydrive.App.router.navigate( redirect, { trigger: true } );
		},

		itemUpdateError: function( response ) {
			this.model.set( 'saving', false );

			if ( response.message ) {
				this.options.warnings.set( { type: 'error', message: response.message } );
			}
		},

		removeParentFolder: function( event ) {
			event.preventDefault();

			$( event.currentTarget ).closest( '#buddydrive-item-folder' ).remove();

			this.privacyDetails();
		},

		updatePrivacyOptions: function( model ) {
			var privacy = model.get( 'selected' ),
				offset = false;

			_.each( this.views._views['#buddydrive-privacy-edit'], function( view, id ) {
				if ( ! _.isUndefined( view.options.filters ) ) {
					offset = id + 1;
				}

				if ( _.isNumber( offset ) && offset <= id ) {
					view.remove();
				}
			} );

			if ( 'password' === privacy ) {
				this.passwordFrame();
			} else if ( 'groups' === privacy || 'folder' === privacy || 'members' === privacy ) {
				this.objectFrame( privacy );
			}
		}
	} );

	if ( typeof BP_Uploader !== 'undefined' ) {
		// BuddyPress Uploader main view
		buddydrive.Views.UploadTool = buddydrive.View.extend( {
			className: 'bp-uploader-window',
			template: buddydrive.template( 'upload-window' ),

			defaults: _.pick( BP_Uploader.settings.defaults, ['container', 'drop_element', 'browse_button'] ),

			initialize: function() {
				this.model = new Backbone.Model( this.defaults );

				// Init the BuddyDrive Uploader tool
				this.on( 'ready', this.initUploader );
			},

			initUploader: function() {
				this.uploader = new buddydrive.Uploader.uploader();

				$( this.uploader ).on( 'bp-uploader-warning', _.bind( this.setWarning, this ) );
				$( this.uploader ).on( 'bp-uploader-new-upload', _.bind( this.resetWarning, this ) );
				$( this.uploader ).on( 'bp-uploader-upload-complete', _.bind( this.resetObject, this ) );
			},

			setWarning: function( event, message ) {
				if ( _.isUndefined( message ) ) {
					return;
				}

				this.options.warnings.set( 'message', message );
			},

			resetWarning: function( event, uploader ) {
				if ( ! _.isUndefined( buddydrive.App.Query.get( 'user_id' ) ) && 'admin' === buddydrive.Settings.buddydrive_scope ) {
					uploader.settings.multipart_params.bp_params.item_id = buddydrive.App.Query.get( 'user_id' );
				}

				if ( ! _.isUndefined( buddydrive.App.Query.get( 'buddydrive_parent' ) ) ) {
					uploader.settings.multipart_params.bp_params.parent_folder_id = buddydrive.App.Query.get( 'buddydrive_parent' );

					// Makes sure if the folder is empty an Admin can send files as the folder owner
					if ( 'admin' === buddydrive.Settings.buddydrive_scope && _.isUndefined( buddydrive.App.Query.get( 'user_id' ) ) && ! _.isUndefined( buddydrive.App.items.options.post_parent_owner ) ) {
						uploader.settings.multipart_params.bp_params.item_id = buddydrive.App.items.options.post_parent_owner;
					}
				}

				if ( 'friends' === buddydrive.Settings.buddydrive_scope ) {
					uploader.settings.multipart_params.bp_params.privacy = buddydrive.Settings.buddydrive_scope;
				}

				this.options.warnings.clear();
			},

			resetObject: function( event, uploader ) {
				_.extend( uploader.settings.multipart_params.bp_params, buddydrive.App.UploadParams );

				// Hide the uploader
				$( '#buddydrive-uploader' ).addClass( 'buddydrive-hide' );

				// Silently reset the action
				this.options.actions.get( 'new_file' ).set( 'active', false, { silent: true } );
			}
		} );

		buddydrive.Views.newFolder = buddydrive.View.extend( {
			tagName:   'div',
			id:        'buddydrive-new-folder',

			events: {
				'click #create-folder': 'createFolder'
			},

			initialize: function() {
				this._views = {};

				if ( this.options.warnings.get( 'message' ) ) {
					this.options.warnings.clear();
				}

				this.views.set( [
					new buddydrive.Views.Input( {
						type        : 'text',
						id          : 'buddydrive-folder-title',
						name        : 'title',
						placeholder : buddydrive.Strings.new_folder_name
					} ),
					new buddydrive.Views.Button( {
						id          : 'create-folder',
						'class'     : 'button-primary button-large',
						text        : buddydrive.Strings.new_folder_button
					} )
				] );
			},

			/**
			 * When the selected filter changes, update the Item Query properties to match.
			 */
			createFolder: function( event ) {
				event.preventDefault();

				var title = $( '#buddydrive-folder-title' ).val(), data = {};

				if ( ! title ) {
					return;
				}

				data = {
					title  : title,
					user_id: buddydrive.App.Query.get( 'user_id' )
				};

				if ( 'friends' === buddydrive.Settings.buddydrive_scope ) {
					data.privacy = buddydrive.Settings.buddydrive_scope;
				}

				buddydrive.App.items.addFolder( {
					data: data,
					error: _.bind( this.folderError, this )
				} );

				this.model.set( 'active', false );
			},

			folderError: function( response ) {
				if ( ! response.message ) {
					return;
				}

				this.options.warnings.set( 'message', response.message );
			}
		} );
	}

} )( buddydrive, jQuery );

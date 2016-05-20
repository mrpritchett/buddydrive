/* globals wp, buddydrive, BuddyDrive_Upgrader, _, Backbone */
window.wp = window.wp || {};
window.buddydrive = window.buddydrive || {};

( function( exports, $ ) {

	if ( typeof BuddyDrive_Upgrader === 'undefined' ) {
		return;
	}

	_.extend( buddydrive, _.pick( wp, 'Backbone', 'ajax', 'template' ) );

	// Init Models and Collections
	buddydrive.Models      = buddydrive.Models || {};
	buddydrive.Collections = buddydrive.Collections || {};

	// Init Views
	buddydrive.Views = buddydrive.Views || {};

	/**
	 * The Upgrader!
	 */
	buddydrive.Upgrader = {
		/**
		 * Launcher
		 */
		start: function() {
			this.tasks = new buddydrive.Collections.Tasks();
			this.completed = false;

			// Create the task list view
			var task_list = new buddydrive.Views.Upgrader( { collection: this.tasks } );

			task_list.inject( '#buddydrive-upgrader' );

			this.setUpTasks();
		},

		/**
		 * Populate the tasks collection
		 */
		setUpTasks: function() {
			var self = this;

			_.each( BuddyDrive_Upgrader.tasks, function( task, index ) {
				if ( ! _.isObject( task ) ) {
					return;
				}

				self.tasks.add( {
					id      : task.action_id,
					order   : index,
					message : task.message,
					count   : task.count,
					done    : 0,
					active  : false
				} );
			} );
		}
	};

	/**
	 * The Tasks collection
	 */
	buddydrive.Collections.Tasks = Backbone.Collection.extend( {
		proceed: function( options ) {
			options         = options || {};
			options.context = this;
			options.data    = options.data || {};

			options.data = _.extend( options.data, {
				action              : 'buddydrive_upgrader',
				'_buddydrive_nonce' : BuddyDrive_Upgrader.nonce
			} );

			return buddydrive.ajax.send( options );
		}
	} );

	/**
	 * Extend Backbone.View with .prepare() and .inject()
	 */
	buddydrive.View = buddydrive.Backbone.View.extend( {
		inject: function( selector ) {
			this.render();
			$( selector ).html( this.el );
			this.views.ready();
		},

		prepare: function() {
			if ( ! _.isUndefined( this.model ) && _.isFunction( this.model.toJSON ) ) {
				return this.model.toJSON();
			} else {
				return {};
			}
		}
	} );

	/**
	 * List of tasks view
	 */
	buddydrive.Views.Upgrader = buddydrive.View.extend( {
		tagName   : 'div',

		initialize: function() {
			this.views.add( new buddydrive.View( { tagName: 'ul', id: 'buddydrive-tasks' } ) );

			this.collection.on( 'add', this.injectTask, this );
			this.collection.on( 'change:active', this.manageQueue, this );
			this.collection.on( 'change:done', this.manageQueue, this );
		},

		taskSuccess: function( response ) {
			var task, next, nextTask;

			if ( response.done && response.action_id ) {
				task = this.get( response.action_id );

				task.set( 'done', Number( response.done ) + Number( task.get( 'done' ) ) );

				if ( Number( task.get( 'count' ) ) === Number( task.get( 'done' ) ) ) {
					task.set( 'active', false );

					next     = Number( task.get( 'order' ) ) + 1;
					nextTask = this.findWhere( { order: next } );

					if ( _.isObject( nextTask ) ) {
						nextTask.set( 'active', true );
					} else {
						$( '.dashboard_page_buddydrive-upgrade #message' ).removeClass( 'buddydrive-hide' );
					}
				}
			}
		},

		taskError: function( response ) {
			if ( response.message && response.action_id ) {
				if ( 'warning' === response.type ) {
					var task = this.get( response.action_id );
					response.message = response.message.replace( '%d', Number( task.get( 'count' ) ) - Number( task.get( 'done' ) ) );
				}

				$( '#' + response.action_id + ' .buddydrive-progress' ).html( response.message ).addClass( response.type );
			} else {
				$( '.dashboard_page_buddydrive-upgrade #message' ).html( '<p>' + response.message + '</p>' ).removeClass( 'buddydrive-hide updated' ).addClass( 'error' );
			}
		},

		injectTask: function( task ) {
			this.views.add( '#buddydrive-tasks', new buddydrive.Views.Task( { model: task } ) );
		},

		manageQueue: function( task ) {
			if ( true === task.get( 'active' ) ) {
				this.collection.proceed( {
					data    : _.pick( task.attributes, ['id', 'count', 'done'] ),
					success : this.taskSuccess,
					error   : this.taskError
				} );
			}
		}
	} );

	/**
	 * The task view
	 */
	buddydrive.Views.Task = buddydrive.View.extend( {
		tagName   : 'li',
		template  : buddydrive.template( 'progress-window' ),
		className : 'buddydrive-task',

		initialize: function() {
			this.model.on( 'change:done', this.taskProgress, this );
			this.model.on( 'change:active', this.addClass, this );

			if ( 0 === this.model.get( 'order' ) ) {
				this.model.set( 'active', true );
			}
		},

		addClass: function( task ) {
			if ( true === task.get( 'active' ) ) {
				$( this.$el ).addClass( 'active' );
			}
		},

		taskProgress: function( task ) {
			if ( ! _.isUndefined( task.get( 'done' ) ) && ! _.isUndefined( task.get( 'count' ) ) ) {
				var percent = ( Number( task.get( 'done' ) ) / Number( task.get( 'count' ) ) ) * 100;
				$( '#' + task.get( 'id' ) + ' .buddydrive-progress .buddydrive-bar' ).css( 'width', percent + '%' );
			}
		}
	} );

	buddydrive.Upgrader.start();

} )( buddydrive, jQuery );

/* jshint node:true */
/* global module */
module.exports = function( grunt ) {
	require( 'matchdep' ).filterDev( ['grunt-*', '!grunt-legacy-util'] ).forEach( grunt.loadNpmTasks );
	grunt.util = require( 'grunt-legacy-util' );

	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		jshint: {
			options: grunt.file.readJSON( '.jshintrc' ),
			grunt: {
				src: ['Gruntfile.js']
			},
			all: ['Gruntfile.js', 'includes/js/*.js', 'includes/admin/js/*.js']
		},
		checktextdomain: {
			options: {
				correct_domain: false,
				text_domain: 'buddydrive',
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'_n:1,2,4d',
					'_ex:1,2c,3d',
					'_nx:1,2,4c,5d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: ['**/*.php', '!**/node_modules/**'],
				expand: true
			}
		},
		clean: {
			all: [ 'includes/css/*.min.css', 'includes/js/*.min.js', 'includes/admin/css/*.min.css', 'includes/admin/js/*.min.js' ]
		},
		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					exclude: ['/node_modules'],
					mainFile: 'buddydrive.php',
					potFilename: 'buddydrive.pot',
					processPot: function( pot ) {
						pot.headers['last-translator'] = 'imath <imath@chat.wordpress.org>';
						pot.headers['language-team'] = 'ENGLISH <imath@chat.wordpress.org>';
						return pot;
					},
					type: 'wp-plugin'
				}
			}
		},
		uglify: {
			minify: {
				extDot: 'last',
				expand: true,
				ext: '.min.js',
				src: ['includes/**/*.js', '!*.min.js', '!includes/js/buddydrive.js', '!includes/js/buddydrive-view.js', '!includes/admin/js/buddydrive-admin.js']
			},
			options: {
				banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
				'<%= grunt.template.today("UTC:yyyy-mm-dd h:MM:ss TT Z") %> - ' +
				'https://github.com/imath/buddydrive/ */\n'
			}
		},
		cssmin: {
			minify: {
				extDot: 'last',
				expand: true,
				ext: '.min.css',
				src: ['includes/**/*.css', '!*.min.css', '!includes/css/buddydrive.css'],
				options: {
					banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
					'<%= grunt.template.today("UTC:yyyy-mm-dd h:MM:ss TT Z") %> - ' +
					'https://github.com/imath/buddydrive/ */'
				}
			}
		},
		jsvalidate:{
			src: 'includes/**/*.js',
			options:{
				globals: {},
				esprimaOptions:{},
				verbose: false
			}
		}
	});


	/**
	 * Register tasks.
	 */
	grunt.registerTask( 'commit',  ['checktextdomain'] );

	grunt.registerTask( 'jstest', ['jsvalidate', 'jshint'] );

	grunt.registerTask( 'shrink', ['clean', 'cssmin', 'uglify'] );

	grunt.registerTask( 'release', ['checktextdomain', 'makepot', 'clean', 'jstest', 'cssmin', 'uglify'] );

	// Default task.
	grunt.registerTask( 'default', ['commit'] );
};

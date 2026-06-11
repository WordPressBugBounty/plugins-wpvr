module.exports = function( grunt ) {
    'use strict';

    const fs = require( 'fs' ),
        pkgInfo = grunt.file.readJSON( 'package.json' ),
        isWin = process.platform === 'win32',
        npm = isWin ? 'cmd' : 'npm',
        composer = isWin ? 'cmd' : 'composer',
        npmArgs = isWin ? ['/c', 'npm'] : [],
        composerArgs = isWin ? ['/c', 'composer'] : [];


    // Project configuration
    grunt.initConfig( {
        pkg: pkgInfo,
        copy: require( './.grunt-config/copy' ),
        clean: {
            main: ['production/']
        },
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './production/wpvr-v' + pkgInfo.version + '.zip'
				},
				expand: true,
				cwd: 'production/',
				src: ['**/*'],
				dest: 'wpvr'
			}
		},
        replace: require( './.grunt-config/replace' ),
        run: {
            options: {},
            build: {
                cmd: npm,
                args: npmArgs.concat(['run', 'build'])
            },
            removeDev: {
                cmd: composer,
                args: composerArgs.concat(['install', '--no-dev', '--ignore-platform-reqs', '--optimize-autoloader'])
            },
            dumpautoload: {
                cmd: composer,
                args: composerArgs.concat(['dumpautoload', '-o'])
            }
        }
    } );

    grunt.loadNpmTasks('grunt-run');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    // grunt.loadNpmTasks('grunt-replace');
	grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-text-replace');

    // Default task(s).
    grunt.registerTask( 'default', [
        'run:build',
        'run:removeDev',
        'run:dumpautoload',
    ] );


    grunt.registerTask( 'copy-check', [
        'copy',
    ] );


    grunt.registerTask( 'build', [
        'default',
        'clean',
        'replace',
        'copy',
    ] );
	grunt.registerTask('zip', [
		'compress',
	]);
};

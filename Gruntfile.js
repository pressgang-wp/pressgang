/**
 * Gruntfile for PressGang release packaging.
 *
 * In the parent theme this is used solely to create distributable zip archives.
 * Child themes often use Grunt extensively as a task runner (SASS compilation,
 * JS bundling, watch tasks, etc.), but PressGang is not prescriptive about tooling.
 *
 * Usage:
 *   grunt build   — packages the theme into release/pressgang.<version>.zip
 *
 * The version number is read from package.json.
 */
module.exports = function (grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// Remove any previous release zip for the current version.
		clean: {
			main: ['release/<%= pkg.version %>']
		},

		// Create a zip archive containing only distributable theme files.
		// Dev-only files (node_modules, .git, IDE config, etc.) are excluded.
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './release/pressgang.<%= pkg.version %>.zip'
				},
				expand: true,
				src: [
					'**',
					'!.idea',
					'!node_modules/**',
					'!release/**',
					'!Gruntfile.js',
					'!package.json',
					'!.git/**',
					'!.gitignore',
					'!.gitmodules'
				],
				dest: 'pressgang/'
			}
		},
	});

	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-compress');

	// `grunt build` is the only task — zips the theme for distribution.
	grunt.registerTask('build', ['compress']);

};

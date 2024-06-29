module.exports = function (grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		clean: {
			main: ['release/<%= pkg.version %>']
		},

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

	grunt.registerTask('build', ['compress']);

};

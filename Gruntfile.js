module.exports = function(grunt) {

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
          '!.git/**',
          '!.sass-cache/**',
          '!css/src/**',
          // '!js/src/**',
          '!img/src/**',
          '!Gruntfile.js',
          '!package.json',
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

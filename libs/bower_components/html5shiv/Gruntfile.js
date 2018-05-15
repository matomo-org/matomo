/*global module:false*/
module.exports = function(grunt){

	
	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		bower: grunt.file.readJSON('bower.json'),
		copy: {
			demo: {
				files: [
					{expand: true, src: ['src/*'], dest: 'dist/', filter: 'isFile', flatten: true}
				]
			}
		},

		uglify: {
			options: {
				beautify: {
					ascii_only : true
				},
				preserveComments: 'some'
			},
			html5shiv: {
				files: [{
					expand: true,     // Enable dynamic expansion.
					cwd: 'src/',      // Src matches are relative to this path.
					src: ['**/*.js'], // Actual pattern(s) to match.
					dest: 'dist/',   // Destination path prefix.
					ext: '.min.js'
				}]
			}
		},
		watch: {
			js: {
				files: ['src/**/*.js'],
				tasks: ['copy', 'uglify', 'bytesize']
			}
		},
		bytesize: {
			all: {
				src: [
					'dist/**.min.js'
				]
			}
		}
	});

	
	// Default task.

	

	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-bytesize');

	grunt.registerTask('default', ['copy', 'uglify', 'bytesize', 'watch']);

};

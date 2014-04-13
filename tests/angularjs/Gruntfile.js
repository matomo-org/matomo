module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        watch: {
            scripts: {
                files: ['plugins/**/*.less', 'plugins/**/*.css'],
                tasks: ['clean-pattern'],
                options: {
                    spawn: false,
                },
            },
        },
        "clean-pattern": {
            files: {path: "tmp/assets", pattern: /(.*).css/}
        }
    });

    grunt.loadNpmTasks('clean-pattern');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.file.setBase('../../')

    // Default task(s).
    grunt.registerTask('default', ['watch']);

};
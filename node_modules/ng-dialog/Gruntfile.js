module.exports = function (grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> - v<%= pkg.version %> (<%= pkg.homepage %>) */\n'
            },
            ngDialog: {
                files: {
                    './js/ngDialog.min.js': ['./js/ngDialog.js']
                }
            }
        },
        jshint: {
            options: {
                ignores: ['./js/ngDialog.min.js']
            },
            files: ['*.js']
        },
        myth: {
            dist: {
                files: {
                    './css/ngDialog.css': './css/myth/ngDialog.css',
                    './css/ngDialog-theme-default.css': './css/myth/ngDialog-theme-default.css',
                    './css/ngDialog-theme-plain.css': './css/myth/ngDialog-theme-plain.css'
                }
            }
        },
        cssmin: {
            options: {
                banner: '/*! <%= pkg.name %> - v<%= pkg.version %> (<%= pkg.homepage %>) */\n'
            },
            minify: {
                files: {
                    'css/ngDialog.min.css': ['css/ngDialog.css'],
                    'css/ngDialog-theme-default.min.css': ['css/ngDialog-theme-default.css'],
                    'css/ngDialog-theme-plain.min.css': ['css/ngDialog-theme-plain.css']
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-myth');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    grunt.registerTask('default', ['jshint']);
    grunt.registerTask('build', ['uglify', 'myth', 'cssmin']);
    grunt.registerTask('css', ['myth', 'cssmin']);
};

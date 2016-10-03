module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        karma: {
            unit: {
                configFile: 'karma.conf.js',
                autoWatch: true
            }
        },
        pkg: grunt.file.readJSON('package.json'),
        watch: {
            scripts: {
                files: ['plugins/**/*.less', 'plugins/**/*.css'],
                tasks: ['clean-pattern'],
                options: {
                    spawn: false,
                },
            },
            piwikjs: {
                files: ['js/piwik.js'],
                tasks: ["shell:compilePiwikJs", "shell:updateTracker"],
                options: {
                    spawn: false,
                },
            },
            piwikjs2: {
                files: ['plugins/*/tracker.js',],
                tasks: ["shell:updateTracker"],
                options: {
                    spawn: false,
                },
            }
        },
        "shell": {
            compilePiwikJs: {
                command: "sed '/<DEBUG>/,/<\\/DEBUG>/d' < piwik.js | sed 's/eval/replacedEvilString/' | java -jar yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar --type js --line-break 1000 | sed 's/replacedEvilString/eval/' | sed 's/^[/][*]/\\/*!/' > piwik-min.js && cp piwik-min.js ../piwik.js",
                options: {
                    execOptions: {
                        cwd: 'js'
                    }
                }
            },
            updateTracker: {
                command: "php console custom-piwik-js:update --ignore-minified",
                options: {
                    execOptions: {
                        cwd: ''
                    }
                }
            }
        },
        "clean-pattern": {
            files: {path: "tmp/assets", pattern: /(.*).css/}
        }
    });

    grunt.loadNpmTasks('clean-pattern');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-shell');

    grunt.file.setBase('../../')

    // Default task(s).
    grunt.registerTask('default', ['watch']);

};
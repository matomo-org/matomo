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
            piwikjs: {
                files: ['js/piwik.js'],
                tasks: ["shell:compilePiwikJs"],
                options: {
                    spawn: false,
                },
            }
        },
        "shell": {
            compilePiwikJs: {
                command: "sed '/<DEBUG>/,/<\\/DEBUG>/d' < piwik.js | sed 's/eval/replacedEvilString/' | java -jar yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar --type js --line-break 1000 | sed 's/replacedEvilString/eval/' | sed 's/^[/][*]/\\/*!/' > piwik-min.js && cp piwik-min.js ../piwik.js",
                options: {
                    execOptions: {
                        cwd: 'js'
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
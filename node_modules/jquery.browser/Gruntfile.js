module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    browserify: {
      'test/test-require.js': ['test/src/require/test.js'],
      options: {
        banner: '/*\n\n ****** Testing Require file ****** \n\n*/'
      }
    },
    jshint: {
      files: ['Gruntfile.js', 'dist/jquery.browser.js', 'test/test.js'],

      options: {
        globals: {
          jQuery: true,
          console: true,
          module: true
        }
      }
    },
    uglify: {
      options: {
        banner: '/*!\n * jQuery Browser Plugin <%= pkg.version %>\n * https://github.com/gabceb/jquery-browser-plugin\n *\n * Original jquery-browser code Copyright 2005, 2015 jQuery Foundation, Inc. and other contributors\n * http://jquery.org/license\n *\n * Modifications Copyright <%= grunt.template.today("yyyy") %> Gabriel Cebrian\n * https://github.com/gabceb\n *\n * Released under the MIT license\n *\n * Date: <%= grunt.template.today("dd-mm-yyyy")%>\n */'
      },
      dist: {
        files: {
          'dist/<%= pkg.name %>.min.js': 'dist/<%= pkg.name %>.js'
        }
      }
    },
    copy: {
      main: {
        src: "dist/<%= pkg.name %>.js",
        dest: "test/src/<%= pkg.name %>.js"
      }
    },
    exec: {
      'test-jquery': {
        command: "casperjs test test/test.js",
        stdout: true,
        stderr: true
      },
      'test-require': {
        command: "./node_modules/.bin/mocha test/test-require.js",
        stdout: true,
        stderr: true
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-exec');
  grunt.loadNpmTasks('grunt-browserify');

  grunt.registerTask('default', ['jshint', 'uglify', 'copy']);

  grunt.registerTask('test-jquery', ['default', 'exec:test-jquery']);
  grunt.registerTask('test-require', ['default', 'browserify', 'exec:test-require']);
  grunt.registerTask('test', ['test-jquery', 'test-require']);
};

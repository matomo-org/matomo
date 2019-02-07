/*global module:false*/
module.exports = function(grunt) {
  'Use strict';

  // show elapsed time at the end
  require('time-grunt')(grunt);

  // load all grunt tasks
  //require('load-grunt-tasks')(grunt);
  require('jit-grunt')(grunt, {
    'bump-only': 'grunt-bump',
    'bump-commit': 'grunt-bump',
    coveralls: 'grunt-karma-coveralls'
  });

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    meta: {
      bannerLocal:
        '/*! iFrame Resizer (iframeSizer.min.js ) - v<%= pkg.version %> - ' +
        '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
        ' *  Desc: Force cross domain iframes to size to content.\n' +
        ' *  Requires: iframeResizer.contentWindow.min.js to be loaded into the target frame.\n' +
        ' *  Copyright: (c) <%= grunt.template.today("yyyy") %> David J. Bradshaw - dave@bradshaw.net\n' +
        ' *  License: MIT\n */\n',
      bannerRemote:
        '/*! iFrame Resizer (iframeSizer.contentWindow.min.js) - v<%= pkg.version %> - ' +
        '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
        ' *  Desc: Include this file in any page being loaded into an iframe\n' +
        ' *        to force the iframe to resize to the content size.\n' +
        ' *  Requires: iframeResizer.min.js on host page.\n' +
        ' *  Copyright: (c) <%= grunt.template.today("yyyy") %> David J. Bradshaw - dave@bradshaw.net\n' +
        ' *  License: MIT\n */\n'
    },

    clean: ['coverage', 'coverageLcov'],

    qunit: {
      files: ['test/*.html']
    },

    karma: {
      options: {
        configFile: 'karma.conf.js'
      },
      travis: {
        singleRun: true,
        browsers: ['PhantomJS'],
        coverageReporter: {
          type: 'lcov',
          dir: 'coverageLcov/'
        }
      },
      single: {
        singleRun: true,
        browsers: ['Chrome', 'Firefox'] // 'Safari', 'PhantomJS'
      }
    },

    coveralls: {
      options: {
        debug: true,
        coverageDir: 'coverageLcov',
        dryRun: false,
        force: true,
        recursive: true
      }
    },

    jshint: {
      options: {
        asi: true,
        eqeqeq: true,
        laxbreak: true,
        globals: {
          jQuery: false,
          require: true,
          process: true
        }
      },
      gruntfile: {
        src: 'gruntfile.js'
      },
      code: {
        src: 'src/**/*.js'
      }
    },

    uglify: {
      options: {
        sourceMap: true,
        sourceMapIncludeSources: true,
        report: 'gzip'
      },
      local: {
        options: {
          banner: '<%= meta.bannerLocal %>',
          sourceMapName: 'js/iframeResizer.map'
        },
        src: ['js/iframeResizer.js'],
        dest: 'js/iframeResizer.min.js'
      },
      remote: {
        options: {
          banner: '<%= meta.bannerRemote %>',
          sourceMapName: 'js/iframeResizer.contentWindow.map'
        },
        src: ['js/iframeResizer.contentWindow.js'],
        dest: 'js/iframeResizer.contentWindow.min.js'
      },
      polyfil: {
        options: {
          banner: '// IE8 polyfils for iframeResizer.js\n',
          sourceMapName: 'js/ie8.polyfils.map'
        },
        src: ['src/ie8.polyfils.js'],
        dest: 'js/ie8.polyfils.min.js'
      }
    },

    watch: {
      files: ['src/**/*'],
      tasks: 'default'
    },

    bump: {
      options: {
        files: ['package.json', 'package-lock.json', 'bower.json', 'iframeResizer.jquery.json'],
        updateConfigs: ['pkg'],
        commit: true,
        commitMessage: 'Release v%VERSION%',
        commitFiles: ['-a'], // '-a' for all files
        createTag: true,
        tagName: 'v%VERSION%',
        tagMessage: 'Version %VERSION%',
        push: true,
        pushTo: 'origin',
        gitDescribeOptions: '--tags --always --abbrev=1 --dirty=-d' // options to use with '$ git describe'
      }
    },

    shell: {
      options: {
        stdout: true,
        stderr: true,
        failOnError: true
      },
      npm: {
        command: 'npm publish'
      },
      deployExample: {
        command: function() {
          var retStr = '',
            fs = require('fs');

          if (fs.existsSync('bin')) {
            retStr = 'bin/deploy.sh';
          }

          return retStr;
        }
      }
    },

    jsonlint: {
      json: {
        src: ['*.json']
      }
    },

    removeBlock: {
      options: ['TEST CODE START', 'TEST CODE END'],
      files: [
        {
          src: 'src/iframeResizer.contentWindow.js',
          dest: 'js/iframeResizer.contentWindow.js'
        }
      ]
    },

    copy: {
      main: {
        nonull: true,
        src: 'src/iframeResizer.js',
        dest: 'js/iframeResizer.js'
      }
    }
  });

  grunt.registerTask('default', ['notest', 'karma:single']);
  grunt.registerTask('build', ['removeBlock', 'copy', 'uglify']);
  grunt.registerTask('notest', ['jsonlint', 'jshint', 'build']);
  grunt.registerTask('test', ['clean', 'jshint', 'karma:single', 'qunit']);
  grunt.registerTask('travis', [
    'clean',
    'notest',
    'qunit',
    'karma:travis',
    'coveralls'
  ]);

  grunt.registerTask('postBump', ['build', 'bump-commit', 'shell']);
  grunt.registerTask('preBump', ['clean', 'notest']);
  grunt.registerTask('patch', ['preBump', 'bump-only:patch', 'postBump']);
  grunt.registerTask('minor', ['preBump', 'bump-only:minor', 'postBump']);
  grunt.registerTask('major', ['preBump', 'bump-only:major', 'postBump']);

  grunt.registerMultiTask('removeBlock', function() {
    // set up a removal regular expression
    var removalRegEx = new RegExp(
      '(// ' +
        this.options()[0] +
        ' //)(?:[^])*?(// ' +
        this.options()[1] +
        ' //)',
      'g'
    );

    this.data.forEach(function(fileObj) {
      var sourceFile = grunt.file.read(fileObj.src),
        removedFile = sourceFile.replace(removalRegEx, ''),
        targetFile = grunt.file.write(fileObj.dest, removedFile);
    }); // for each loop end
  });
};

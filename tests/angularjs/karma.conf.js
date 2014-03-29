// Karma configuration
// Generated on Fri Mar 28 2014 16:33:02 GMT+1300 (NZDT)

module.exports = function(config) {
  config.set({

    // base path that will be used to resolve all patterns (eg. files, exclude)
    basePath: '../../',


    // frameworks to use
    // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
    frameworks: ['mocha'],


    // list of files / patterns to load in the browser
    files: [
        'tests/angularjs/bootstrap.js',
        'node_modules/expect.js/index.js',
        'libs/angularjs/angular.min.js',
        'libs/angularjs/angular-mocks.js',
        'libs/jquery/jquery.js',
        'plugins/CoreHome/angularjs/common/services/service.js',
        'plugins/CoreHome/angularjs/common/filters/filter.js',
        'plugins/CoreHome/angularjs/common/directives/directive.js',
        'plugins/CoreHome/angularjs/piwikApp.js',
        'plugins/*/angularjs/**/*.js',
        'plugins/*/angularjs/**/*_test.js'
    ],


    // list of files to exclude
    exclude: [
    ],


    // preprocess matching files before serving them to the browser
    // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
    preprocessors: {
    
    },


    // test results reporter to use
    // possible values: 'dots', 'progress'
    // available reporters: https://npmjs.org/browse/keyword/karma-reporter
    reporters: ['progress'],


    // web server port
    port: 9876,

    callback: '__karma__.start()',

    // enable / disable colors in the output (reporters and logs)
    colors: true,


    // level of logging
    // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
    logLevel: config.LOG_INFO,


    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: true,


    // start these browsers
    // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
    browsers: ['Chrome', 'PhantomJS'],


    // Continuous Integration mode
    // if true, Karma captures browsers, runs the tests and exits
    singleRun: false
  });
};

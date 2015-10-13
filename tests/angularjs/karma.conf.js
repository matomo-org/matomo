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
        'tests/angularjs/node_modules/chai/chai.js',
        'tests/angularjs/bootstrap.js',
        'libs/bower_components/angular/angular.min.js',
        "libs/bower_components/angular-sanitize/angular-sanitize.js",
        "libs/bower_components/angular-animate/angular-animate.js",
        'libs/bower_components/angular-mocks/angular-mocks.js',
        'libs/bower_components/jquery/dist/jquery.min.js',
        "libs/bower_components/jquery-ui/jquery-ui.min.js",
        "plugins/CoreHome/javascripts/require.js",
        "plugins/Morpheus/javascripts/piwikHelper.js",
        "plugins/Morpheus/javascripts/ajaxHelper.js",
        "plugins/CoreHome/javascripts/broadcast.js",
        'plugins/CoreHome/angularjs/common/services/service.module.js',
        'plugins/CoreHome/angularjs/common/filters/filter.module.js',
        'plugins/CoreHome/angularjs/common/directives/directive.module.js',
        'plugins/CoreHome/angularjs/piwikApp.js',
        'plugins/*/angularjs/**/*.js',
        'piwik.js',
        'plugins/AnonymousPiwikUsageMeasurement/javascripts/url.js',
        'plugins/AnonymousPiwikUsageMeasurement/javascripts/tracking.js',
        'plugins/*/**/*.spec.js'
    ],

    // list of files to exclude
    exclude: [
    ],

    // preprocess matching files before serving them to the browser
    // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
    preprocessors: {
        "plugins/*/angularjs/**/*.js": ['jshint']
    },

    // test results reporter to use
    // possible values: 'dots', 'progress'
    // available reporters: https://npmjs.org/browse/keyword/karma-reporter
    reporters: ['spec', 'mocha'],

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
    browsers: ['PhantomJS'],

    // Continuous Integration mode
    // if true, Karma captures browsers, runs the tests and exits
    singleRun: false
  });
};

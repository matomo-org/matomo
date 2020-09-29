var testMinified = process.argv.indexOf('--min') > -1, subject;

if (testMinified) {
    subject = 'js/ngDialog.min.js';
    console.log('Testing minifed ngDialog');
} else {
    subject = 'js/ngDialog.js';
}

module.exports = function(config) {
    config.set({
        basePath: '',
        frameworks: ['jasmine'],
        files: [
            'bower_components/angular/angular.js',
            'bower_components/angular-mocks/angular-mocks.js',
            'example/inside-directive/**/*.js',
            subject,
            'tests/unit/**/*.js'
        ],
        port: 9877,
        colors: true,
        logLevel: config.LOG_INFO,
        autoWatch: false,
        browsers: ['PhantomJS'],
        singleRun: false,
        reporters: ['dots', 'coverage'],
        preprocessors: {
            'js/ngDialog.js': ['coverage']
        },
        plugins: [
            'karma-phantomjs-launcher',
            'karma-coverage',
            'karma-jasmine'
        ],
        coverageReporter: {
          reporters: [{
                type: 'html',
                subdir: 'report-html'
          }, {
                type: 'lcov',
                subdir: 'report-lcov'
          }]
        }
    });
};

## Installation:

`[sudo] npm install .`

#### Ubuntu
On Ubuntu you might be able to use the `scripts/install-ubuntu.sh` script. Have a [look](https://github.com/matomo-org/piwik/blob/master/tests/angularjs/install-ubuntu.sh) and give it a try

#### Requirements
* node.js > 0.10
* npm

## Used libraries
* [Karma](http://karma-runner.github.io/0.12/index.html)
* [Chai](http://chaijs.com/guide/styles/)
* [Mocha](http://visionmedia.github.io/mocha/)
* You need more assertions? Have a look at [Chai plugins](http://chaijs.com/plugins)

## File structure

We do not have a general `tests` folder containing all test files. Instead we create a file having the same name appended by `.spec.js` in the same directory.

For instance you want to test a file named `startfrom.js` then we create a file named `startfrom.spec.js`:
`plugins/CoreHome/angularjs/common/filters/startfrom.js` =>
`plugins/CoreHome/angularjs/common/filters/startfrom.spec.js`

## Execution

Run tests and run them automatically again once there is a file change:
`karma start karma.conf.js`

Run test suites only once:
`karma start karma.conf.js --single-run`

Run test suites in a different browser:
`karma start karma.conf.js --browsers Chrome`

Run tests in different browser permanently:
You can change the `browsers: ['Chrome', 'Firefox', 'PhantomJS']` property in `karma.conf.js` but you might have to be careful to not commit this change.

Before executing a test it'll always run [JSHint](http://www.jshint.com/) to detect and report possible problems in the JavaScript code.

## Testing existing jQuery code

Just in case you want to write a test for your jQuery code you can do this the same way. You might be interested in the [Chai jQuery](http://chaijs.com/plugins/chai-jquery) plugin.

## Examples
* [Testing a filter](../../plugins/CoreHome/angularjs/common/filters/startfrom.spec.js)
* [Testing a directive](../../plugins/CoreHome/angularjs/common/directives/autocomplete-matched.spec.js)
* [Testing a service/provider/factory/model](../../plugins/CoreHome/angularjs/common/services/piwik.spec.js)
* See more examples in [AngularJS guide](http://docs.angularjs.org/guide/unit-testing)

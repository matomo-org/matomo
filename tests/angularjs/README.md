## Installation:

`[sudo] npm install .`

#### Ubuntu
On Ubuntu you might be able to use the `scripts/install-ubuntu.sh` script. Have a [look](https://github.com/piwik/piwik/blob/master/tests/angularjs/install-ubuntu.sh) and give it a try

#### Requirements
* node.js > 0.10
* npm

## Used libraries
* [Karma](http://karma-runner.github.io/0.12/index.html)
* [Chai](http://chaijs.com/guide/styles/)
* [Mocha](http://visionmedia.github.io/mocha/)
* You need more assertions? Have a look at [Chai plugins](http://chaijs.com/plugins)

## Execution

Run tests and run them automatically again once there is a file change:
`karma start karma.conf.js`

Run test suites only once:
`karma start karma.conf.js --single-run`

Run test suites in a different browser:
`karma start karma.conf.js --browsers Chrome`

Run tests in different browser permanently:
You can change the `browsers: ['Chrome', 'Firefox', 'PhantomJS']` property in `karma.conf.js` but you might have to be careful to not commit this change.

## Testing existing jQuery code

TBD

## Examples
* [Testing a filter](plugins/CoreHome/angularjs/common/filters/startfrom_test.js)
* See more examples in [AngularJS guide](http://docs.angularjs.org/guide/unit-testing)

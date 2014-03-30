## Installation:

npm install .

### Ubuntu
On Ubuntu you might be able to use the `scripts/install-ubuntu.sh` script. Have a [look](https://github.com/piwik/piwik/blob/master/tests/angularjs/install-ubuntu.sh) and give it a try

## Used libs
MochaJS + Chai

## Execution

Run tests (it runs tests automatically again once there is a file change)
`karma start karma.conf.js`

It runs the tests in PhantomJS at the moment. If you want to run it in different browsers
just change the `browsers: ['Chrome', 'Firefox', 'PhantomJS']` property in `karma.conf.js`

## Testing existing jQuery code

TBD

## Help

* [Chai](http://chaijs.com/guide/styles/)
* [Mocha](http://visionmedia.github.io/mocha/)
* You need more assertions? Have a look at [Chai plugins](http://chaijs.com/plugins)

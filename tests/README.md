Piwik comes with unit tests, integration tests, system tests, Javascript tests and UI tests.
This document briefly describes how to use and modify Piwik tests.

## Continuous Integration

We use Travis CI for our continuous integration server. It automatically runs our battery of thousands of unit/integration/screenshot tests
after each commit to our GIT repo. More information at the links:

 * Piwik on Travis CI: https://travis-ci.org/piwik/piwik
 * QA in Piwik: http://piwik.org/qa/

Each core Piwik developer is responsible to keep the build green. If a developer breaks the build, he will receive an email from Travis CI.

The next section explains how you can run the test suite on your own dev machine.

## How To Run Piwik Tests

To run tests, you must install Piwik via Git and set it up for development. A guide for this is available in our [Developer Zone](http://developer.piwik.org/guides/getting-started-part-1). The part about "Creating a plugin" can be skipped.

To execute the tests:

 * In your php.ini make sure you have the setting to show all errors:
 `error_reporting = E_ALL | E_STRICT`

 * Go to tests/index.html to see the tests homepage
   and run the Integration tests via a visual UI, or run JS Tests

 * Next you will need to install PHPUnit

## PHPUnit Tests

1. 	To install PHPUnit, run `php composer.phar install --dev` in the Piwik root directory.

2.	Ensure the `[database_tests]` section in `piwik/config/config.php.ini` is set up correctly,
	i.e. with the correct password to prevent the following error:
	`SQLSTATE[28000] [1045] Access denied for user 'root'@'localhost' (using password: NO)`

3. 	Run the tests

    $ cd /path/to/piwik
    $ ./console tests:run --testsuite unit
    $ ./console tests:run --testsuite integration
    $ ./console tests:run --testsuite system

	There are also two main groups of tests: core and plugins
	For example run `./console tests:run core` to run all Core Piwik tests.

	You can combine testsuite and groups like this:
	`./console tests:run --testsuite unit core`. This would run all unit tests in core.
	`./console tests:run --testsuite integration CustomAlerts`. This would run all integration tests of the CustomAlerts plugin.
	`./console tests:run CustomAlerts`. This would run all unit, integration and system tests of the CustomAlerts plugin. (group only)

	To execute multiple groups you can separate them via a comma:
	`./console tests:run CustomAlerts,Insights`. This would run all unit, integration and system tests of the CustomAlerts and Insights plugin.

4.	Write more tests :)
	See ["Writing Unit tests with PHPUnit"](http://www.phpunit.de/manual/current/en/writing-tests-for-phpunit.html)

## How to differentiate between unit, integration or system tests?

This can be sometimes hard to decide and often leads to discussions. We consider a test as a unit test when
it tests only a single method or class. Sometimes two or three classes can still be considered as a Unit for instance if
 you have to pass a dummy class or something similar but it should actually only test one class or method.
  If it has a dependency to the filesystem, web, config, database or to other plugins it is not a unit test but an
  integration test. If the test is slow it is most likely not a unit test but an integration test as well.
  "Slow" is of course very subjective and also depends on the server but if your test does not have any dependencies
your test will be really fast.

It is an integration test if you have any dependency to a loaded plugin, to the filesystem, web, config, database or something
similar. It is an integration test if you test multiple classes in one test.

It is a system test if you - for instance - make a call to Piwik itself via HTTP or CLI and the whole system is being tested.

### Why do we split tests in unit, integration, system and ui folders?

Because they fail for different reasons and the duration of the test execution is different. This allows us to execute
all unit tests and get a result very quick. Unit tests should not fail on different systems and just run everywhere for
 example no matter whether you are using NFS or not. Once the unit tests are green one would usually execute all integration
 tests to see whether the next stage works. They take a bit longer as they have dependencies to the database and filesystem.
 The system and ui tests take the most time to run as they always run through the whole code.

Another advantage of running the tests separately is that we are getting a more accurate code coverage. For instance when
running the unit tests we will get the true code coverage as they always only test one class or method. Integration tests
usually run through a lot of code but often actually only one method is supposed to be tested. Although many methods are
not tested they would be still marked as tested when running integration tests.

## System Tests

System tests files are in `tests/PHPUnit/System/*Test.php`

System tests allow to test how major Piwik components interact together.
A test will typically generate hits to the Tracker (record visits and page views)
and then test all API responses and for each API output. It then checks that they match expected XML (or CSV, json, etc.).
If a test fails, you can compare the processed/ and expected/ directories in a graphical text compare tool, such as WinMerge on Win, or MELD on Linux, or even with PhpStorm, to easily view changes between files.

For example using Meld, click on "Start new comparison", "Directory comparison",
in "Original" select "path/to/piwik/tests/PHPUnit/System/expected"
in "Mine" select "path/to/piwik/tests/PHPUnit/System/processed"

If changes are expected due to the code changes you make, simply copy the file from processed/ to expected/, and test will then pass. Copying files is done easily using Meld (ALT+LEFT).
Otherwise, if you didn't expect to modify the API outputs, it might be that your changes are breaking some features unexpectedly.

### Fixtures for System tests

System tests use Fixtures to generate controlled web usage data (visits, goals, pageviews, events, site searches, content tracking, custom variables, etc.).

Fixtures are stored in [tests/PHPUnit/Fixtures](https://github.com/piwik/piwik/tree/master/tests/PHPUnit/Fixtures)

#### OmniFixture

We also have an OmniFixture that includes all other Fixtures. OmniFixture is used for screenshot tests to provide data across most reports. 

#### Keep OmniFixture up to date

Remember to update the [Omnifixture SQL dump](https://github.com/piwik/piwik/blob/master/tests/resources/OmniFixture-dump.sql.gz) whenever you make any change to any fixture. You can use:

    ./console tests:setup-fixture OmniFixture --sqldump=OmniFixture-dump.sql
    cat OmniFixture-dump.sql | gzip > tests/resources/OmniFixture-dump.sql.gz

Keeping the OmniFixture up to date makes it easier to see which tests fail after each small fixture change. 

If we don't update the OmniFixture then we end up with many failed screenshots tests which makes it hard to see whether those changes are expected or not.

### Scheduled Reports Tests

As part of our system tests we generate the scheduled reports (in HTML, PDF & SMS).
Some of these scheduled reports contain PNG graphs. Depending on the system under test, generated images can differ.
Therefore, PNG graphs are only tested and compared against "expected" graphs, if the system under test has the same characteristics as the integration server.
The characteristics of the integration server are described in `SystemTestCase::canImagesBeIncludedInScheduledReports()`

### Running tests on Ubuntu

If you use Ubuntu or another Linux distribution, you must make one change to the filesystem configuration to make tests run fast. [Read more here](https://github.com/piwik/piwik/blob/master/tests/README.troubleshooting.md#important-note-for-linux-users-fix-for-slow-tests).

## JavaScript Tests

piwik.js is unit tested and you can run the Javascript tests at: /piwik/tests/javascript/

## Testing Data

See [tests/README.testing-data.md](https://github.com/piwik/piwik/blob/master/tests/README.testing-data.md) to import testing data in Piwik.

## UI Screenshots Tests

See [tests/README.screenshots.md](https://github.com/piwik/piwik/blob/master/tests/README.screenshots.md)

## Build artifacts

### Download build artifacts for any recent commit

You can retrieve the files generated during the build (the build artifacts) at [builds-artifacts.piwik.org](http://builds-artifacts.piwik.org/)

### Test logs on CI

By default tests running on Travis CI will log all messages of at least `INFO` level in `$PIWIK_ROOT_DIR/tmp/logs/piwik.log`. In a given travis build output, you can view the logs by clicking on the line `$ cat $PIWIK_ROOT_DIR/tmp/logs/piwik.log` at the end of the build output text.

Note: `DEBUG` and `VERBOSE` messages are not logged by default (to keep Travis page loading fast). At any time you can temporarirly enable logging by [modifying this file](https://github.com/piwik/piwik/blob/master/tests/PHPUnit/config.ini.travis.php#L23-27) and changing `log_level = info` to `log_level = debug` or `log_level = verbose`.

### Screenshot tests build artifacts

The screenshot tests generated by the continuous integration server are uploaded in [builds-artifacts.piwik.org/ui-tests.master/](http://builds-artifacts.piwik.org/ui-tests.master/?C=M;O=D)

## Troubleshooting

See [tests/README.troubleshooting.md](https://github.com/piwik/piwik/blob/master/tests/README.troubleshooting.md) for troubleshooting the tests.

## Advanced users

### Debugging tests

As a software developer writing tests it can be useful to be able to set breakpoints and debug while running tests. If you use Phpstorm [read this answer](http://stackoverflow.com/a/14998884/3759928) to learn to configure Phpstorm with the PHPUnit from Composer. 


### Benchmarks

See [tests/PHPUnit/Benchmarks/README.md](https://github.com/piwik/piwik/blob/master/tests/PHPUnit/Benchmarks/README.md) to learn about running Benchmark tests.

### Profiling

See [tests/README.xhprof.md](https://github.com/piwik/piwik/blob/master/tests/README.xhprof.md) for help on how to profile Piwik with XHProf.



## Participate

You can help by improving existing tests, or identify some missing tests and implement them.
See http://piwik.org/participate/development-process
Please contact us at hello@piwik.org


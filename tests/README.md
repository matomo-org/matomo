Piwik comes with unit tests, integration tests, Javascript tests and Webtests.
This document briefly describes how to use and modify Piwik tests. 

## Continuous Integration

We use Travis CI for our continuous integration server. It automatically runs our battery of thousands of unit/integration/screenshot tests
after each commit to our GIT repo. More information at the links:

 * Piwik on Travis CI: https://travis-ci.org/piwik/piwik
 * QA in Piwik: http://piwik.org/qa/

Each core Piwik developer is responsible to keep the build green. If a developer breaks the build, he will receive an email from Travis CI.

The next section explains how you can run the test suite on your own dev machine.

## How To Run Piwik Tests

To run tests, you must use the Git master. Tests files are not in the Piwik zip archive.

You can get the latest Git revision at: http://github.com/piwik/piwik

```
$ git clone https://github.com/piwik/piwik.git
```

Next install Composer which will lets you download the libraries used in Piwik:
```
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```

To execute the tests:

 * In your php.ini make sure you have the setting to show all errors:
 `error_reporting = E_ALL | E_STRICT`

 * Go to tests/index.html to see the tests homepage
   and run the Integration tests via a visual UI, or run JS Tests

 * Next you will need to install PHPUnit

## PHPUnit Tests

1. 	To install PHPUnit, run `php composer.phar update` in the Piwik root directory.

    Add the PHPUnit binary path to the your PATH environment variable. For example on Linux:
    Edit `.bashrc` in your home directory and add the following line:

        export PATH=/path/to/dir:$PATH

    You will need to source your `.bashrc` or logout/login (or restart the terminal) for the changes to take effect.
    To source your `.bashrc`, in your home directory simply type

        $ source .bashrc

    See [PHPUnit doc](http://www.phpunit.de/manual/current/en/installation.html).
    Note: if you were already using PHPUnit using PEAR, you may delete the PEAR PHPUnit with `sudo rm /usr/bin/phpunit`

2. 	Configure PHPUnit: Copy the file `piwik/tests/PHPUnit/phpunit.xml.dist` to `phpunit.xml`.
	In this file, you will find the following lines.
	Please edit HTTP_HOST and REQUEST_URI to match the hostname and path of the Piwik files.
    For example if your Piwik is available at http://localhost/path/to/piwik/ you would write:

		<server name="HTTP_HOST" value="localhost"/>
		<server name="REQUEST_URI" value="/path/to/piwik/"/>

3.	Ensure the `[database_tests]` section in `piwik/config/config.php.ini` is set up correctly, 
	i.e. with the correct password to prevent the following error:
	`SQLSTATE[28000] [1045] Access denied for user 'root'@'localhost' (using password: NO)`

4. 	Run the tests

	$ cd /path/to/piwik/tests/PHPUnit
	$ phpunit --group Core
     	$ phpunit --group Plugins
     	$ phpunit --group Integration

	There are three main groups of tests: Core, Plugins and Integration
	For example run `phpunit --group Core`
	to run all Core Piwik tests.
	
5.	Write more tests :)
	See ["Writing Unit tests with PHPUnit"](http://www.phpunit.de/manual/current/en/writing-tests-for-phpunit.html)



## Integration Tests

Integration tests files are in `tests/PHPUnit/Integration/*Test.php`

Integration tests allow to test how major Piwik components interact together.
A test will typically generate hits to the Tracker (record visits and page views)
and then test all API responses and for each API output. It then checks that they match expected XML (or CSV, json, etc.).
If a test fails, you can compare the processed/ and expected/ directories in a graphical
text compare tool, such as WinMerge on Win, or MELD on Linux, to easily view changes between files.

For example using Meld, click on "Start new comparison", "Directory comparison",
in "Original" select "path/to/piwik/tests/PHPUnit/Integration/expected"
in "Mine" select "path/to/piwik/tests/PHPUnit/Integration/processed"

If changes are expected due to the code changes you make, simply copy the file from processed/ to
expected/, and test will then pass. Copying files is done easily using Meld (ALT+LEFT).
Otherwise, if you didn't expect to modify the API outputs, it might be that your changes are breaking some features unexpectedly.

### Scheduled Reports Tests

As part of our integration tests we generate the scheduled reports (in HTML, PDF & SMS). 
Some of these scheduled reports contain PNG graphs. Depending on the system under test, generated images can differ.
Therefore, PNG graphs are only tested and compared against "expected" graphs, if the system under test has the same characteristics as the integration server.
The characteristics of the integration server are described in `IntegrationTestCase::canImagesBeIncludedInScheduledReports()`

## JavaScript Tests

piwik.js is unit tested and you can run the Javascript tests at: /piwik/tests/javascript/

## Testing Data

See [tests/README.testing-data.md](https://github.com/piwik/piwik/blob/master/tests/README.testing-data.md) to import testing data in Piwik.

## UI Screenshots Tests

See [tests/README.screenshots.md](https://github.com/piwik/piwik/blob/master/tests/README.screenshots.md)

## Profiling

See [tests/README.xhprof.md](https://github.com/piwik/piwik/blob/master/tests/README.xhprof.md) for help on how to profile Piwik with XHProf.

## Benchmarks

See [tests/PHPUnit/Benchmarks/README.md](https://github.com/piwik/piwik/blob/master/tests/PHPUnit/Benchmarks/README.md) to learn about running Benchmark tests.

## Build artifacts

You can retrieve the files generated during the build (the build artifacts) at [builds-artifacts.piwik.org](http://builds-artifacts.piwik.org/)

## Troubleshooting 

See [tests/README.troubleshooting.md](https://github.com/piwik/piwik/blob/master/tests/README.troubleshooting.md) for troubleshooting the tests.

## Participate

You can help by improving existing tests, or identify some missing tests and implement them.
See http://piwik.org/participate/development-process
Please contact us at hello@piwik.org


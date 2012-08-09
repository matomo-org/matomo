Piwik comes with unit tests, integration tests, Javascript tests and Webtests.
This document briefly describes how to use and modify Piwik tests. 
 
 HOW TO RUN PIWIK TESTS
=======================
To run tests, you must use the SVN trunk. Tests files are not in the Piwik zip archive.
You can get the latest SVN at: http://dev.piwik.org/svn/
To execute the tests:
* Go to tests/index.php to see the various tests available
* Run all unit tests and integration tests by calling the file tests/all_tests.php in your browser.
* Run only integration tests by calling tests/integration_tests.php 

You can also run one test file at a time by executing the test file directly eg. 
http://path/to/piwik/trunk/tests/core/DataTable.test.php

You can also run tests from the command line:
  $ cd path/to/piwik
  $ find . -name '*.test.php' -exec php {} \;

 UNIT TESTs
===========
Unit tests use the Simpletest Unit Testing framework.
Plugins can also integrate their own unit tests,  in a tests/ directory inside the plugin folder.
Check for example the plugins plugins/SitesManager/tests/ or plugins/UserCountry/tests/

 INTEGRATION TESTS
==================
Integration tests files are in tests/integration/*.test.php

Integration tests allow to test how major Piwik components interact together.
A test will typically generate hits to the Tracker (record visits and page views)
and then test all API responses and for each API output. It then checks that they match expected XML (or CSV, json, etc.).
If a test fails, you can compare the processed/ and expected/ directories in a graphical 
text compare tool, such as WinMerge on Win, to easily view changes between files.
If changes are expected due to the code changes you make, simply copy the file from processed/ to 
expected/, and test will then pass. Otherwise, if you didn't expect to modify the API outputs, 
it might be that your changes are breaking some features unexpectedly.

To run all integration tests at once, execute tests/integration_tests.php
See also http://dev.piwik.org/trac/ticket/1465   

 PHPUNIT TESTS
==============
1) install PHPUnit on your system
	$ cd your/php/directory
	$ sudo pear upgrade PEAR
	$ pear config-set auto_discover 1
	$ pear install --alldeps pear.phpunit.de/PHPUnit
	Doc at: http://www.phpunit.de/manual/current/en/installation.html

2) Configure PHPUnit: Copy the file piwik/tests/PHPUnit/phpunit.xml.dist as phpunit.xml.
	In this file, you will find the following lines.
	Please edit HTTP_HOST and REQUEST_URI to match the hostname and path of the Piwik files:
    <server name="HTTP_HOST" value="localhost"/>
    <server name="REQUEST_URI" value="/path/to/piwik/tests/all_tests.php"/>
	
3) Run the tests (see the next section to run tests in the browser)
	$ cd /path/to/piwik/tests/PHPUnit
	$ phpunit
	This will run all unit + integration tests. It might take 30 minutes to run.

	You can also run tests of speciified "parts" of Piwik.
	There are three main groups of tests: Core, Plugins and Integration
	For example run
	$ phpunit --group Core
	to run all Core Piwik tests. You may also combine groups like
	$ phpunit --group Core,Plugins

4) Write more tests :)
	See "Writing Unit tests with PHPUnit" 
	http://www.phpunit.de/manual/current/en/writing-tests-for-phpunit.html

 VISUALPHPUNIT
=================
Piwik comes with a modified copy of VisualPHPUnit (see https://github.com/NSinopoli/VisualPHPUnit)
which you can use to run PHPUnit tests in your browser.

- Starting VisualPHPUnit -

To load VisualPHPUnit point your browser to http://path/to/piwik/trunk/tests/lib/visualphpunit/.

VisualPHPUnit will already be configured for use with Piwik. You may, however, need to set the
'pear_path' config option manually. You'll know you need to do this if PHP cannot require the
necessary files. To set this option, edit the file located at

/path/to/piwik/trunk/tests/lib/visualphpunit/app/config/bootstrap.php

and set the 'pear_path' config option.

- Running tests -

Once VisualPHPUnit is loaded, you can run tests by selecting files or whole directories in the
file selector on the left of the screen, then clicking the 'Run tests' button. To select
files/directories ctrl+click them.

To run all Piwik tests, ctrl+click the 'Core', 'Integration' and 'Plugins' directory, then
click the 'Run tests' button.

- Running tests by URL -

If you're in need of a URL that will not only load VisualPHPUnit but run one or more tests,
you may add the list of tests to run as the hash of the URL. For example,

http://path/to/piwik/trunk/tests/lib/visualphpunit/#/Core/DataTableTest.php:/Core/CookieTest.php

will load VisualPHPUnit and immediately run the tests in DataTableTest.php and CookieTest.php.
Currently, this feature will not allow you to specify directories with tests to run.

- Using phpunit.xml -

By default, VisualPHPUnit lets you run tests by selecting individual test files or directories
and clicking the 'Run Tests' button. If you want to use a phpunit.xml file, either your own or the
one that comes with Piwik, you'll need to modify VisualPHPUnit's configuration. Edit the file
located at

/path/to/piwik/trunk/tests/lib/visualphpunit/app/config/bootstrap.php

and set the 'xml_configuration_file' config option.

Please note that when a phpunit.xml file is supplied in the configuration, VisualPHPUnit will
always run tests with it, regardless of what files you select. You can override this behavior
in the web UI by selecting 'No' in the 'Use XML Config' input.

- Debugging invalid responses -

Sometimes, VisualPHPUnit will run PHPUnit tests and get a response it can't read. These problems
are usually caused by an unmatched ob_start() call in the code somewhere, or by the program
prematurely exiting.

To find the cause of such issues, it can help to determine what code can & can't affect the
output VisualPHPUnit sees. Code that can affect what VisualPHPUnit sees is before the bug in
question, and code that can't is after it.

 JAVASCRIPT TESTS
=================
piwik.js is unit tested and you can run tests via piwik/tests/javascript/

 WEBTESTS
=========
The Installation process and few other important tasks are also "webtested". 
These webtests are ran by the continuous integration server Jenkins.
http://dev.piwik.org/svn/trunk/tests/webtest/testcases/

 CONTINOUS INTEGRATION
======================
We currently use Jenkins as continuous integration build server. More information:
http://piwik.org/qa/

 PARTICIPATE
============
You can help by improving existing tests, or identify some missing tests and implement them.
See http://piwik.org/participate/development-process
Please contact us at hello@piwik.org

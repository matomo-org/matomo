Piwik comes with unit tests, integration tests, Javascript tests and Webtests.
This document briefly describes how to use and modify Piwik tests. 
 
 HOW TO RUN PIWIK TESTS
=======================
To run tests, you must use the SVN trunk. Tests files are not in the Piwik zip archive.
You can get the latest SVN at: http://dev.piwik.org/svn/
To execute the tests:
* Go to tests/index.php to see the various tests available

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
	$ sudo pear install --alldeps pear.phpunit.de/PHPUnit
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

VisualPHPUnit will already be configured for use with Piwik. 

Troubleshooting
 * If at this URL you see a listing of files instead of seeing VisualPHPUnit, 
   enable mod_rewrite apache module, and make sure your vhost in apache 
   configuration has "AllowOverride all" so that .htaccess are loaded.
 
 * If you get an error such as "Warning: require_once(PHPUnit/Autoload.php)" it is because the PEAR path 
   is not set in your php.ini. Edit in php.ini the value include_path to include the path to your
   PEAR setup, and restart Apache.

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

 BENCHMARKS
=================
Piwik comes with a system that can be used to benchmark certain Piwik processes. The benchmarking
system relies both on PHPUnit and VisualPHPUnit.

- Benchmarks & Fixtures -

Piwik's benchmarks are written as unit tests. Except, they don't setup the database by themselves.
Instead, there are several 'fixture' classes that do the setup. You can mix and match different
benchmarks with different fixtures to time piwik processes under differing circumstances.

For example, you can test how long it takes to generate reports for one site with ~230,000 visits
in one day, or you can test how long it takes to generate reports for 1,000 sites w/ 12 visits
each on one day, simply by changing the fixture.

- Running Benchmarks -

To run a benchmark, first load VisualPHPUnit by pointing your browser to:

http://path/to/piwik/trunk/tests/lib/visualphpunit/

  * On the left you will see a list of files and directories. Click on the 'Benchmarks' directory
    to expand it. Then click on the 'Fixtures' directory to expand it.

  * Click one of the benchmarks to run (see the next section for a list of benchmarks).

  * Below the file listing is a section with the title 'GLOBALS'. In order to run a benchmark,
    you'll have to enter some information here.

  * Enter 'PIWIK_BENCHMARK_FIXTURE' in the left input. In the right input, pick one of the fixtures
    in the 'Fixtures' folder and enter it (w/o the .php extension). For example, you can enter
    'SqlDump' or 'ThousandSitesTwelveVisitsEachOneDay' (see the next section for a list of fixtures).

  * Click the 'Add' link in the 'GLOBALS' section. In the new row enter 'PIWIK_BENCHMARK_DATABASE'
    in the left input. On the right enter the name of a new database. This database will be created
    and saved so you don't have to re-setup the database next time you run a benchmark. If you
    plan on running the benchmark more than once, this can save a lot of time.
    
    NOTE: This option isn't required.
  
  * Now, click the 'Run Tests' link at the top of the page. This will run the test, which can take
    a long time based on how fast your machine is. When the test finishes, you'll see the following
    statistics:
    
    * Total Elapsed Time - the amount of time it took to run the test + setup the fixture + process
                           PHPUnit's result + etc.
    * Total Execution Time - the amount of time it took to run the test (this is an important
                             metric).
    * Peak Memory Use - The peak memory use for the test (this is an important metric).
    * Total Memory Delta - The memory delta of every test run, summed up.

NOTE: You cannot at present run more than one benchmark, so make sure you only select one.

- Included Benchmarks and Fixtures -

These are the benchmarks currently written for Piwik:

  * Benchmarks/ArchivingProcessBenchmark.php
    
    This benchmark times the process Piwik uses to generate reports and calculate metrics.
  
  * Benchmarks/TrackerBenchmark.php
  
    This benchmark times how long it takes to track 12,500 pageviews in one bulk request.

These are the fixtures currently included with Piwik:

  * Benchmarks/Fixtures/OneSiteTwelveThousandVisitsOneYear.php
    
    This fixture adds one website and tracks twelve thousand visits over the course of
    a year (1,000 visits per month).
  
  * Benchmarks/Fixtures/ThousandSitesTwelveVisitsEachOneDay.php
    
    This fixture adds one thousand websites and tracks 12 visits each on one day.
  
  * Benchmarks/Fixtures/SqlDump.php
    
    This fixture downloads and loads an SQL dump. The SQL dump is for a database with one
    website with ~230,000 visits on one day. There are around ~2.3 pageviews per visit and
    each visit resulted in at least one conversion.

- Benchmarking with git -

If you use git, you can use the benchmarking system to easily see if there are performance
regressions caused by your changes.

To do this, make sure you put your changes into a new git branch. You can create a new
branch by running: 
    $ git checkout -b branch_name

Run a benchmark using the branch without changes ('master'). Load VisualPHPUnit in a new
tab and switch branches to the new branch. You can switch branches by running:
    $ git checkout branch_name

In the new tab run the benchmark again. You can now compare how long it took to run the
test w/o your changes and with your changes.

NOTE:
  - You don't need git to do this, but it's much easier w/ git.
  - It's a good idea to make sure the tests pass before benchmarking.

 PROFILING WITH XHPROF
======================

Piwik is distributed with a copy of XHProf, the PHP profiler created by Facebook. Piwik
also comes with a copy of VisualPHPUnit that has been modified to easily use XHProf. Using
these two tools, you can profile tests and benchmarks.

- Installing XHProf -

First, XHProf must be built (this guide assumes you're using a linux variant):

    * Navigate to the XHProf extension directory.
      $ cd /path/to/piwik/trunk/tests/lib/xhprof-0.9.2/extension
    
    * Build XHProf.
      $ ./configure
      $ make
    
      xhprof.so will now exist in the ./modules directory.
    
    * Configure PHP to use XHProf. Add the following to your php.ini file:
      
      [xhprof]
      extension=/path/to/piwik/trunk/tests/lib/xhprof-0.9.2/extension/modules/xhprof.so
      xhprof.output_dir=/path/to/output/dir
      
      Replace /path/to/output/dir with an existing directory. All your profiles will be
      stored there.

Restart your webserver and you're done. VisualPHPUnit will automatically detect if XHProf
is installed and act accordingly.

- Using XHProf -

To use XHProf, first load VisualPHPUnit by pointing your browser to:

http://path/to/piwik/trunk/tests/lib/visualphpunit/

Select a test or get ready to run a benchmark. Make sure the 'Profile with XHProf' select
box is set to 'Yes' and click 'Run Tests'.

When the test finishes, a link will be displayed that will let you view the profile that
was created.

NOTE:
    * Currently, it is not possible to use XHProf with more than one test, so if multiple
      tests are selected, XHProf will not be used.
    * XHProf will not delete old profiles, you must do that yourself, though individual
      profiles do not take much space.

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

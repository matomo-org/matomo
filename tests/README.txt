Piwik comes with unit tests, integration tests, Javascript tests and Webtests.
This document briefly describes how to use and modify Piwik tests. 
 
 HOW TO RUN PIWIK TESTS
=======================
To run tests, you must use the SVN trunk. Tests files are not in the Piwik zip archive.
You can get the latest SVN at: http://dev.piwik.org/svn/
Run all tests by calling the file tests/all_tests.php in your browser.
The file will run all unit tests and integration tests.

You can also run one test file at a time by executing the test file directly eg. 
http://path/to/piwik/trunk/tests/core/DataTable.test.php

 UNIT TESTs
===========
Unit tests use the Simpletest Unit Testing framework.
Plugins can also integrate their own unit tests, 
in a tests/ directory inside the plugin folder.
Check for example the plugins plugins/SitesManager/tests/ or plugins/UserCountry/tests/

 INTEGRATION TESTS
==================
Integration tests allow to test how major Piwik components interact together.
A test will typically generate hits to the Tracker (record visits and page views)
and then test all API responses and for each API output. It then checks that they match expected XML (or CSV, json, etc.).
If a test fails, you can compare the processed/ and expected/ directories in a graphical 
text compare tool, such as WinMerge on Win, to easily view changes between files.
If changes are expected due to the code changes you make, simply copy the file from processed/ to 
expected/, and test will then pass. Otherwise, if you didn't expect to modify the API outputs, 
it might be that your changes are breaking some features unexpectedly.

The main test is located tests/integration/Main.test.php
See also http://dev.piwik.org/trac/ticket/1465   

 JAVASCRIPT TESTS
=================
piwik.js is unit tested and you can run tests in piwik/tests/javascript/

 WEBTESTS
=========
The Installation process is also webtested. They are ran by the continuous integration server.
http://dev.piwik.org/svn/trunk/tests/webtest/testcases/level0/

 CONTINOUS INTEGRATION
======================
We currently use Hudson as continuous integration build server. More information:
http://piwik.org/qa/

 PARTICIPATE
============
You can help by improving existing tests, or identify some missing tests and implement them.
See http://dev.piwik.org/trac/wiki/PiwikDevelopmentProcess#Howtosubmitapatch
Please contact us at hello@piwik.org

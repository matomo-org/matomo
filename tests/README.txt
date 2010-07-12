Piwik comes with unit tests, integration tests, Javascript tests and Webtests.
This document briefly describes how to use and modify Piwik tests. 
 
 HOW TO RUN PIWIK TESTS
=======================
You can run all tests by calling the file tests/all_tests.php
The file will run all unit tests and integration tests.

You can also run one test file at a time by executing the test file directly eg. 
http://dev.piwik.org/svn/trunk/tests/core/DataTable.test.php

 UNIT TESTs
===========
Piwik tests use the Simpletest Unit Testing framework.
Piwik unit tests suite can be found in the Piwik SVN: 
http://dev.piwik.org/svn/trunk/tests/

Plugins can also integrate unit tests, in a tests/ directory inside the plugin folder.
Check for example plugins/SitesManager/tests/ or plugins/UserCountry/tests/

 INTEGRATION TESTS
==================
Integration tests allow to test how major Piwik components interact together.
A test will typically generate hits to the Tracker (record visits and page views)
and then test all API responses and for each API output. It then checks that they match expected XML.
You can then use Text Compare softwares (eg. WinMerge on Win) to easily view changes.

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
We currently use continuous integration build server, located at:
http://bamboo.openx.org:8085/browse/PIWIK-TRUNK/

 PARTICIPATE
============
You can help by improving existing tests, or identify some missing tests and implement them.
See http://dev.piwik.org/trac/wiki/PiwikDevelopmentProcess#Howtosubmitapatch
Please contact us at hello@piwik.org

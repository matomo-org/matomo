 RUN TESTS
==========
You can run all tests 
http://dev.piwik.org/svn/trunk/tests/all_tests.php

You can run one test file at a time by going to the test file directly 
http://dev.piwik.org/svn/trunk/tests/core/DataTable.test.php

 CONTINOUS INTEGRATION
======================
We currently use continuous integration build server, located at:
http://bamboo.openx.org:8085/browse/PIWIK-TRUNK/

 UNIT TESTs
===========
Piwik tests use the Simpletest Unit Testing framework.

Piwik unit tests suite can be found in the Piwik SVN: 
http://dev.piwik.org/svn/trunk/tests/

 INTEGRATION TESTS
==================
Located in tests/integration/. They generate hits to the Tracker (record visits and page views)
and then test all API responses and for each API output, check that they match expected XML.
You can then use Text Compare softwares (eg. WinMerge on Win) to easily view changes.

 JAVASCRIPT TESTS
=================
piwik.js is unit tested and you can run tests in piwik/tests/javascript/

 WEBTESTS
=========
The Installation process is also webtested. They are ran by the continuous integration server.
http://dev.piwik.org/svn/trunk/tests/webtest/testcases/level0/

If you want to help, we are always looking to improve the Piwik code coverage!  
Please contact us at hello@piwik.org
 
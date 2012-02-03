<?php
require_once 'TestRunner.php';
$runner = new TestRunner('integration');
$runner->init();
$runner->requireBrowser();
$runner->requireDatabase();
$runner->setTestDirectories(array('/tests/integration'));
$runner->run();


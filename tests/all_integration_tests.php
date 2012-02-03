<?php
require_once 'test_runner.php';
$runner = new TestRunner('integration');
$runner->requireBrowser();
$runner->requireDatabase();
$runner->setTestDirectories(array('/tests/integration'));
$runner->run();


<?php
// $Id: parse_error_test.php,v 1.4 2007/05/08 22:08:06 lastcraft Exp $
require_once('../unit_tester.php');
require_once('../reporter.php');

$test = &new TestSuite('This should fail');
$test->addFile('test_with_parse_error.php');
$test->run(new HtmlReporter());
?>
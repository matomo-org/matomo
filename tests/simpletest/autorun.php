<?php
    /**
     *	Autorunner which runs all tests cases found in a file
     *	that includes this module.
     *	@package	SimpleTest
     *	@version	$Id: autorun.php,v 1.9 2007/07/07 00:31:03 lastcraft Exp $
     */
    require_once dirname(__FILE__) . '/unit_tester.php';
    require_once dirname(__FILE__) . '/mock_objects.php';
    require_once dirname(__FILE__) . '/collector.php';
    require_once dirname(__FILE__) . '/default_reporter.php';

    $GLOBALS['SIMPLETEST_AUTORUNNER_INITIAL_CLASSES'] = get_declared_classes();
    register_shutdown_function('simpletest_autorun');

    function simpletest_autorun() {
        if (tests_have_run()) {
			return;
        }
        $candidates = array_intersect(
                capture_new_classes(),
                classes_defined_in_initial_file());
        $loader = new SimpleFileLoader();
        $suite = $loader->createSuiteFromClasses(
                basename(initial_file()),
                $loader->selectRunnableTests($candidates));
        $result = $suite->run(new DefaultReporter());
        if (SimpleReporter::inCli()) {
            exit($result ? 0 : 1);
        }
    }

	function tests_have_run() {
        if ($context = SimpleTest::getContext()) {
			if ($context->getTest()) {
				return true;
			}
		}
		return false;
	}
	
	function initial_file() {
		static $file = false;
		if (! $file) {
			$file = reset(get_included_files());
		}
		return $file;
	}
	
	function classes_defined_in_initial_file() {
        if (! preg_match_all('~class\s+(\w+)~', file_get_contents(initial_file()), $matches)) {
			return array();
		}
		return array_map('strtolower', $matches[1]);
	}
	
	function capture_new_classes() {
        global $SIMPLETEST_AUTORUNNER_INITIAL_CLASSES;
        return array_map('strtolower', array_diff(get_declared_classes(),
                              $SIMPLETEST_AUTORUNNER_INITIAL_CLASSES ?
                              $SIMPLETEST_AUTORUNNER_INITIAL_CLASSES : array()));
	}
?>
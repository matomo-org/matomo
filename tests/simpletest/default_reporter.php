<?php
    /**
     *	Optional include file for SimpleTest
     *	@package	SimpleTest
     *	@subpackage	UnitTester
     *	@version	$Id: default_reporter.php,v 1.4 2007/07/07 00:31:03 lastcraft Exp $
     */

    /**#@+
     *	include other SimpleTest class files
     */
    require_once(dirname(__FILE__) . '/simpletest.php');
    require_once(dirname(__FILE__) . '/scorer.php');
    require_once(dirname(__FILE__) . '/reporter.php');
    require_once(dirname(__FILE__) . '/xml.php');
    /**#@-*/
    
	/**
	 *    Parser for command line arguments. Extracts
	 *    the a specific test to run and engages XML
	 *    reporting when necessary.
	 *    @package SimpleTest
	 *    @subpackage UnitTester
	 */
	class SimpleCommandLineParser {
		var $_to_property = array(
				'case' => '_case', 'c' => '_case',
				'test' => '_test', 't' => '_test',
				'xml' => '_xml', 'x' => '_xml');
		var $_case = '';
		var $_test = '';
		var $_xml = false;
		
		function SimpleCommandLineParser($arguments) {
			if (! is_array($arguments)) {
				return;
			}
			foreach ($arguments as $i => $argument) {
				if (preg_match('/^--?(test|case|t|c)=(.+)$/', $argument, $matches)) {
					$property = $this->_to_property[$matches[1]];
					$this->$property = $matches[2];
				} elseif (preg_match('/^--?(test|case|t|c)$/', $argument, $matches)) {
					$property = $this->_to_property[$matches[1]];
					if (isset($arguments[$i + 1])) {
						$this->$property = $arguments[$i + 1];
					}
				} elseif (preg_match('/^--?(xml|x)$/', $argument)) {
					$this->_xml = true;
				}
			}
		}
		
		function getTest() {
			return $this->_test;
		}
		
		function getTestCase() {
			return $this->_case;
		}
		
		function isXml() {
			return $this->_xml;
		}
	}

    /**
     *    The default reporter used by SimpleTest's autorun
     *    feature. The actual reporters used are dependency
     *    injected and can be overridden.
	 *	  @package SimpleTest
	 *	  @subpackage UnitTester
     */
    class DefaultReporter extends SimpleReporterDecorator {
        
        /**
         *  Assembles the appopriate reporter for the environment.
         */
        function DefaultReporter() {
            if (SimpleReporter::inCli()) {
				global $argv;
				$parser = new SimpleCommandLineParser($argv);
				$interfaces = $parser->isXml() ? array('XmlReporter') : array('TextReporter');
                $reporter = &new SelectiveReporter(
						SimpleTest::preferred($interfaces),
                        $parser->getTestCase(),
                        $parser->getTest());
            } else {
                $reporter = &new SelectiveReporter(
						SimpleTest::preferred('HtmlReporter'),
                        @$_GET['c'],
                        @$_GET['t']);
            }
            $this->SimpleReporterDecorator($reporter);
        }
    }
?>
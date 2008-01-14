<?php
    /**
     *	base include file for SimpleTest
     *	@package	SimpleTest
     *	@subpackage	UnitTester
     *	@version	$Id$
     */

    /**#@+
     *	include other SimpleTest class files
     */
    require_once(dirname(__FILE__) . '/browser.php');
    require_once(dirname(__FILE__) . '/xml.php');
    require_once(dirname(__FILE__) . '/test_case.php');
    /**#@-*/

    /**
     *    Runs an XML formated test on a remote server.
	 *	  @package SimpleTest
	 *	  @subpackage UnitTester
     */
    class RemoteTestCase {
        var $_url;
        var $_dry_url;
        var $_size;
        
        /**
         *    Sets the location of the remote test.
         *    @param string $url       Test location.
         *    @param string $dry_url   Location for dry run.
         *    @access public
         */
        function RemoteTestCase($url, $dry_url = false) {
            $this->_url = $url;
            $this->_dry_url = $dry_url ? $dry_url : $url;
            $this->_size = false;
        }
        
        /**
         *    Accessor for the test name for subclasses.
         *    @return string           Name of the test.
         *    @access public
         */
        function getLabel() {
            return $this->_url;
        }

        /**
         *    Runs the top level test for this class. Currently
         *    reads the data as a single chunk. I'll fix this
         *    once I have added iteration to the browser.
         *    @param SimpleReporter $reporter    Target of test results.
         *    @returns boolean                   True if no failures.
         *    @access public
         */
        function run(&$reporter) {
            $browser = &$this->_createBrowser();
            $xml = $browser->get($this->_url);
            if (! $xml) {
                trigger_error('Cannot read remote test URL [' . $this->_url . ']');
                return false;
            }
            $parser = &$this->_createParser($reporter);
            if (! $parser->parse($xml)) {
                trigger_error('Cannot parse incoming XML from [' . $this->_url . ']');
                return false;
            }
            return true;
        }
        
        /**
         *    Creates a new web browser object for fetching
         *    the XML report.
         *    @return SimpleBrowser           New browser.
         *    @access protected
         */
        function &_createBrowser() {
            $browser = &new SimpleBrowser();
            return $browser;
        }
        
        /**
         *    Creates the XML parser.
         *    @param SimpleReporter $reporter    Target of test results.
         *    @return SimpleTestXmlListener      XML reader.
         *    @access protected
         */
        function &_createParser(&$reporter) {
            $parser = &new SimpleTestXmlParser($reporter);
            return $parser;
        }
        
        /**
         *    Accessor for the number of subtests.
         *    @return integer           Number of test cases.
         *    @access public
         */
        function getSize() {
            if ($this->_size === false) {
                $browser = &$this->_createBrowser();
                $xml = $browser->get($this->_dry_url);
                if (! $xml) {
                    trigger_error('Cannot read remote test URL [' . $this->_dry_url . ']');
                    return false;
                }
                $reporter = &new SimpleReporter();
                $parser = &$this->_createParser($reporter);
                if (! $parser->parse($xml)) {
                    trigger_error('Cannot parse incoming XML from [' . $this->_dry_url . ']');
                    return false;
                }
                $this->_size = $reporter->getTestCaseCount();
            }
            return $this->_size;
        }
    }
?>
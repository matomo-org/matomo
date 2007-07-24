<?php
// Call phpDocumentor_setupTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "phpDocumentor_setupTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'phpDocumentor/Setup.inc.php';

/**
 * Test class for phpDocumentor_setup.
 */
class Documentation_tests_bug1602961 extends PHPUnit_Framework_TestCase {

    private $ps;

    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("phpDocumentor_setupTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
        $GLOBALS['_phpDocumentor_install_dir'] = ".";
        $GLOBALS['_phpDocumentor_setting']['quiet'] = "true";
//        $this->ps = new phpDocumentor_setup;
//        setTitle("Unit Testing");    // this step is necessary to ensure ps->render is instantiated
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
    }

    /**
     * verify the fuzzy logic that guesses
     * the intent of boolean arg values 
     */
    public function testDecideOnOrOff() {

        // verify all expected values are interpreted correctly
        $this->assertFalse(decideOnOrOff('off'));
        $this->assertTrue(decideOnOrOff('on'));
        
        // verify all the fuzzy values we know to recognize
        //   no
        $this->assertFalse(decideOnOrOff('Off'));
        $this->assertFalse(decideOnOrOff('OFF'));
        $this->assertFalse(decideOnOrOff('no'));
        $this->assertFalse(decideOnOrOff('No'));
        $this->assertFalse(decideOnOrOff('NO'));
        $this->assertFalse(decideOnOrOff('false'));
        $this->assertFalse(decideOnOrOff('False'));
        $this->assertFalse(decideOnOrOff('FALSE'));
        $this->assertFalse(decideOnOrOff(0));
                
        //   yes
        $this->assertTrue(decideOnOrOff(''));
        $this->assertTrue(decideOnOrOff('On'));
        $this->assertTrue(decideOnOrOff('ON'));
        $this->assertTrue(decideOnOrOff('y'));
        $this->assertTrue(decideOnOrOff('Y'));
        $this->assertTrue(decideOnOrOff('yes'));
        $this->assertTrue(decideOnOrOff('Yes'));
        $this->assertTrue(decideOnOrOff('YES'));
        $this->assertTrue(decideOnOrOff('true'));
        $this->assertTrue(decideOnOrOff('True'));
        $this->assertTrue(decideOnOrOff('TRUE'));
        $this->assertFalse(decideOnOrOff(1));
                
        // unexpected
        $this->assertTrue(decideOnOrOff('  '));
        $this->assertFalse(decideOnOrOff());
        $this->assertFalse(decideOnOrOff(-1));
        $this->assertFalse(decideOnOrOff(10));
        $this->assertFalse(decideOnOrOff("ash nazg durbatuluk"));
    }

}

// Call phpDocumentor_setupTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "phpDocumentor_setupTest::main") {
    phpDocumentor_setupTest::main();
}
?>

 	  	 

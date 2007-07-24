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
class Documentation_tests_bug1620470 extends PHPUnit_Framework_TestCase {

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
        $this->ps = new phpDocumentor_setup;
        $this->ps->setTitle("Unit Testing");    // this step is necessary to ensure ps->render is instantiated
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
     * verify the anti-pathing security measures
     * on the various converter name pieces 
     */
    public function testCleanConverterNamePiece() {

        define('CHARACTERS_ALLOWED_IN_PRIMARY', '');
        define('CHARACTERS_ALLOWED_IN_SECONDARY', '\/');
        define('CHARACTERS_ALLOWED_IN_TERTIARY', '.\/');

        // verify all existing converter names are accepted
        //     CHM, HTML, PDF, XML (called with one arg)
        $this->assertEquals("CHM",              $this->ps->cleanConverterNamePiece("CHM"));
        $this->assertEquals("HTML",             $this->ps->cleanConverterNamePiece("HTML"));
        $this->assertEquals("PDF",              $this->ps->cleanConverterNamePiece("PDF"));
        $this->assertEquals("XML",              $this->ps->cleanConverterNamePiece("XML"));
        //     CHM, HTML, PDF, XML (called with two args)
        $this->assertEquals("CHM",              $this->ps->cleanConverterNamePiece("CHM",       CHARACTERS_ALLOWED_IN_PRIMARY));
        $this->assertEquals("HTML",             $this->ps->cleanConverterNamePiece("HTML",      CHARACTERS_ALLOWED_IN_PRIMARY));
        $this->assertEquals("PDF",              $this->ps->cleanConverterNamePiece("PDF",       CHARACTERS_ALLOWED_IN_PRIMARY));
        $this->assertEquals("XML",              $this->ps->cleanConverterNamePiece("XML",       CHARACTERS_ALLOWED_IN_PRIMARY));

        //     frames, Smarty, default, DocBook/peardoc2
        $this->assertEquals("frames",           $this->ps->cleanConverterNamePiece("frames",    CHARACTERS_ALLOWED_IN_SECONDARY));
        $this->assertEquals("Smarty",           $this->ps->cleanConverterNamePiece("Smarty",    CHARACTERS_ALLOWED_IN_SECONDARY));
        $this->assertEquals("default",          $this->ps->cleanConverterNamePiece("default",   CHARACTERS_ALLOWED_IN_SECONDARY));
        $this->assertEquals("DocBook/peardoc2", $this->ps->cleanConverterNamePiece("DocBook/peardoc2", CHARACTERS_ALLOWED_IN_SECONDARY));

        //     default, earthli, l0l33t, phpdoc.de, phphtmllib, HandS, PEAR, PHP
        $this->assertEquals("default",          $this->ps->cleanConverterNamePiece("default",   CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("earthli",          $this->ps->cleanConverterNamePiece("earthli",   CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("l0l33t",           $this->ps->cleanConverterNamePiece("l0l33t",    CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("phpdoc.de",        $this->ps->cleanConverterNamePiece("phpdoc.de", CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("phphtmllib",       $this->ps->cleanConverterNamePiece("phphtmllib",CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("HandS",            $this->ps->cleanConverterNamePiece("HandS",     CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("PEAR",             $this->ps->cleanConverterNamePiece("PEAR",      CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("PHP",              $this->ps->cleanConverterNamePiece("PHP",       CHARACTERS_ALLOWED_IN_TERTIARY));

        //     DOM option for default, earthli, l0l33t, phpdoc.de, phphtmllib
        $this->assertEquals("DOM/default",      $this->ps->cleanConverterNamePiece("DOM/default",    CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("DOM/earthli",      $this->ps->cleanConverterNamePiece("DOM/earthli",    CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("DOM/l0l33t",       $this->ps->cleanConverterNamePiece("DOM/l0l33t",     CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("DOM/phpdoc.de",    $this->ps->cleanConverterNamePiece("DOM/phpdoc.de",  CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("DOM/phphtmllib",   $this->ps->cleanConverterNamePiece("DOM/phphtmllib", CHARACTERS_ALLOWED_IN_TERTIARY));

        // verify no up-to-parent pathing is allowed - the resulting converter names are generally invalid
        $this->assertEquals("HTML",             $this->ps->cleanConverterNamePiece("../../HTML"));
        $this->assertEquals("HTML",             $this->ps->cleanConverterNamePiece("../../HTML",   CHARACTERS_ALLOWED_IN_PRIMARY));
        $this->assertEquals("//frames",         $this->ps->cleanConverterNamePiece("../../frames", CHARACTERS_ALLOWED_IN_SECONDARY));
        //    when '.' is allowed to remain, a '..' always returns false to avoid directory traversal
        $this->assertEquals(false,              $this->ps->cleanConverterNamePiece("../../default",CHARACTERS_ALLOWED_IN_TERTIARY));

        $this->assertEquals("varlogHTML",       $this->ps->cleanConverterNamePiece("/var/log/HTML"));
        $this->assertEquals("varlogHTML",       $this->ps->cleanConverterNamePiece("/var/log/HTML",   CHARACTERS_ALLOWED_IN_PRIMARY));
        $this->assertEquals("/var/log/frames",  $this->ps->cleanConverterNamePiece("/var/log/frames", CHARACTERS_ALLOWED_IN_SECONDARY));
        $this->assertEquals("/var/log/default", $this->ps->cleanConverterNamePiece("/var/log/default",CHARACTERS_ALLOWED_IN_TERTIARY));

        // extreme examples of messy input - the resulting converter names are valid
        $this->assertEquals("HTML",             $this->ps->cleanConverterNamePiece("H/.T./M##L"));
        $this->assertEquals("HTML",             $this->ps->cleanConverterNamePiece("H/.T./M##L", CHARACTERS_ALLOWED_IN_PRIMARY));
        $this->assertEquals("frames",           $this->ps->cleanConverterNamePiece("....frames", CHARACTERS_ALLOWED_IN_SECONDARY));

        // extreme examples of messy input - the resulting converter names are invalid
        $this->assertEquals("//frames",         $this->ps->cleanConverterNamePiece("..//.frames",     CHARACTERS_ALLOWED_IN_SECONDARY));
        $this->assertEquals("/./default/./",    $this->ps->cleanConverterNamePiece("/./default/.##/", CHARACTERS_ALLOWED_IN_TERTIARY));
        $this->assertEquals("//default//",      $this->ps->cleanConverterNamePiece("//default//",     CHARACTERS_ALLOWED_IN_TERTIARY));

    }

}

// Call phpDocumentor_setupTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "phpDocumentor_setupTest::main") {
    phpDocumentor_setupTest::main();
}
?>

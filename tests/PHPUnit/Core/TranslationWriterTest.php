<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class TranslationWriterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Dataprovider for testClean
     */
    public function getCleanTestData()
    {
        return array(
            // empty string
            array("", ''),
            // newline
            array("\n", ''),
            // leading and trailing whitespace
            array(" a \n", 'a'),
            // single / double quotes
            array(" &quot;it&#039;s&quot; ", '"it\'s"'),
            // html special characters
            array("&lt;tag&gt;", '<tag>'),
            // other html entities
            array("&hellip;", '…'),
        );
    }

    /**
     * @group Core
     * @group TranslationWriter
     * @dataProvider getCleanTestData
     */
    public function testClean($data, $expected)
    {
        $this->assertEquals($expected, Piwik_TranslationWriter::clean($data));
    }

    /**
     * Dataprovider for testQuote
     */
    public function getQuoteTestData()
    {
        return array(
            // alphanumeric
            array('abc 123', "'abc 123'"),
            // newline
            array("\n", "'\n'"),
            // tab
            array('	', "'	'"),
            // single quote
            array("it's", "'it\'s'"),
        );
    }

    /**
     * @group Core
     * @group TranslationWriter
     * @dataProvider getQuoteTestData
     */
    public function testQuote($data, $expected)
    {
        if (Piwik_Common::isWindows() && $data == "\n") {
            return;
        }
        $this->assertEquals($expected, Piwik_TranslationWriter::quote($data));
    }

    /**
     * @group Core
     * @group TranslationWriter
     */
    public function testGetTranslationPathInvalidLang()
    {
        try {
            $path = Piwik_TranslationWriter::getTranslationPath('../index');
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * @group Core
     * @group TranslationWriter
     */
    public function testGetTranslationPathInvalidBasePath()
    {
        try {
            $path = Piwik_TranslationWriter::getTranslationPath('en', 'core');
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * @group Core
     * @group TranslationWriter
     */
    public function testGetTranslationPath()
    {
        // implicit base path
        $this->assertEquals(PIWIK_INCLUDE_PATH . '/lang/en.php', Piwik_TranslationWriter::getTranslationPath('en'));

        // explicit base path
        $this->assertEquals(PIWIK_INCLUDE_PATH . '/lang/en.php', Piwik_TranslationWriter::getTranslationPath('en', 'lang'));
        $this->assertEquals(PIWIK_INCLUDE_PATH . '/tmp/en.php', Piwik_TranslationWriter::getTranslationPath('en', 'tmp'));
    }

    /**
     * @group Core
     * @group TranslationWriter
     */
    public function testLoadTranslationInvalidLang()
    {
        try {
            $translations = Piwik_TranslationWriter::loadTranslation('a');
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * @group Core
     * @group TranslationWriter
     */
    public function testLoadTranslation()
    {
        require PIWIK_INCLUDE_PATH . '/lang/en.php';
        $this->assertTrue(is_array($translations));

        $englishTranslations = Piwik_TranslationWriter::loadTranslation('en');

        $this->assertEquals(count($translations), count($englishTranslations));
        $this->assertEquals(0, count(array_diff($translations, $englishTranslations)));
        $this->assertEquals(0, count(array_diff_assoc($translations, $englishTranslations)));
    }

    /**
     * @group Core
     * @group TranslationWriter
     */
    public function testSaveTranslation()
    {
        $path = Piwik_TranslationWriter::getTranslationPath('en', 'tmp');

        $translations = array(
            'General_Locale' => 'en_CA.UTF-8',
            'General_Id'     => 'Id',
            'Goals_Goals'    => 'Goals',
            'Plugin_Body'    => "Message\nBody",
        );

        @unlink($path);

        $rc = Piwik_TranslationWriter::saveTranslation($translations, $path);
        $this->assertNotEquals(false, $rc);

        $contents = file_get_contents($path);
        $expected = "<?php\n\$translations = array(\n\t'General_Locale' => 'en_CA.UTF-8',\n\t'General_Id' => 'Id',\n\t'Goals_Goals' => 'Goals',\n\n\t// FOR REVIEW\n\t'Plugin_Body' => 'Message\nBody',\n);\n";
        if (Piwik_Common::isWindows()) $expected = str_replace("\r\n", "\n", $expected);
        $this->assertEquals($expected, $contents);
    }
}

<?php
use Piwik\Common;
use Piwik\TranslationWriter;

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
        $this->assertEquals($expected, TranslationWriter::clean($data));
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
        if (Common::isWindows() && $data == "\n") {
            return;
        }
        $this->assertEquals($expected, TranslationWriter::quote($data));
    }

    /**
     * @group Core
     * @group TranslationWriter
     */
    public function testGetTranslationPathInvalidLang()
    {
        try {
            $path = TranslationWriter::getTranslationPath('../index');
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
            $path = TranslationWriter::getTranslationPath('en', 'core');
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
        $this->assertEquals(PIWIK_INCLUDE_PATH . '/lang/en.json', TranslationWriter::getTranslationPath('en'));

        // explicit base path
        $this->assertEquals(PIWIK_INCLUDE_PATH . '/lang/en.json', TranslationWriter::getTranslationPath('en', 'lang'));
        $this->assertEquals(PIWIK_INCLUDE_PATH . '/tmp/en.json', TranslationWriter::getTranslationPath('en', 'tmp'));
    }

    /**
     * @group Core
     * @group TranslationWriter
     */
    public function testLoadTranslationInvalidLang()
    {
        try {
            $translations = TranslationWriter::loadTranslation('a');
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
        $data = file_get_contents(PIWIK_INCLUDE_PATH . '/lang/en.json');
        $translations = json_decode($data, true);
        $this->assertTrue(is_array($translations));

        $englishTranslations = TranslationWriter::loadTranslation('en');

        $this->assertEquals(count($translations, COUNT_RECURSIVE), count($englishTranslations, COUNT_RECURSIVE));
        foreach($translations as $key => $value) {
            $this->assertEquals(0,
                count(array_diff($translations[$key], $englishTranslations[$key]))
            );
        }
        foreach($translations as $key => $value) {
            $this->assertEquals(0,
                count(array_diff_assoc($translations[$key], $englishTranslations[$key]))
            );
        }
    }

    /**
     * @group Core
     * @group TranslationWriter
     */
    public function testSaveTranslation()
    {
        $path = TranslationWriter::getTranslationPath('en', 'tmp');

        $translations = array(
            'General' => array(
                'Locale' => 'en_CA.UTF-8',
                'Id'     => 'Id'
            ),
            'Goals' => array(
                'Goals'  => 'Goals',
            ),
            'Plugin' => array(
                'Body'    => "Message\nBody"
            )
        );

        @unlink($path);

        $rc = TranslationWriter::saveTranslation($translations, $path);
        $this->assertNotEquals(false, $rc);

        $contents = file_get_contents($path);
        $expected = <<<'EOD'
{
    "General": {
        "Locale": "en_CA.UTF-8",
        "Id": "Id"
    },
    "Goals": {
        "Goals": "Goals"
    }
}
EOD;

        if (Common::isWindows()) $expected = str_replace("\r\n", "\n", $expected);
        $this->assertEquals($expected, $contents);
    }
}

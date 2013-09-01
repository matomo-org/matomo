<?php
use Piwik\Common;
use Piwik\Translate\Writer;

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class WriterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Languages.php';
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Countries.php';
    }

    /**
     * @group Core
     * @group Translate
     * @dataProvider getValidConstructorData
     */
    public function testConstructorValid($language, $plugin)
    {
        $translationWriter = new Writer($language, $plugin);
        $this->assertTrue($translationWriter->hasTranslations());
    }

    public function getValidConstructorData()
    {
        return array(
            array('en', ''),
            array('de', ''),
            array('en', 'ExamplePlugin'),
        );
    }

    /**
     * @group Core
     * @group Translate
     * @expectedException Exception
     */
    public function testConstructorInvalid()
    {
        $translationWriter = new Writer('en', 'InValIdPlUGin');
    }

    /**
     * @group Core
     * @group Translate
     */
    public function testSaveTranslation()
    {
        $translations = array(
            'General' => array(
                'Locale' => 'en_CA.UTF-8',
                'Id'     => 'Id'
            ),
            'Goals'   => array(
                'Goals' => 'Goals',
            ),
            'Plugin'  => array(
                'Body' => "Message\nBody"
            )
        );

        $translationWriter = new Writer('en', '');
        $translationWriter->setTranslations($translations);

        $rc = $translationWriter->saveTemporary();
        $this->assertNotEquals(false, $rc);

        $contents = file_get_contents(PIWIK_DOCUMENT_ROOT.'/tmp/en.json');

        $options = 0;
        if (defined('JSON_UNESCAPED_UNICODE')) $options |= JSON_UNESCAPED_UNICODE;
        if (defined('JSON_PRETTY_PRINT')) $options |= JSON_PRETTY_PRINT;

        $expected = json_encode(json_decode('{"General":{"Locale":"en_CA.UTF-8","Id":"Id"},"Goals":{"Goals":"Goals"}}', true), $options);

        $this->assertEquals($expected, $contents);
    }
}

<?php
use Piwik\Translate\Validate\CoreTranslations;

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class CoreTranslationsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Languages.php';
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Countries.php';
    }

    public function getFilterTestDataValid()
    {
        return array(
            array(
                array(
                    'General' => array_merge(array_fill(0, 251, 'test'), array(
                                                                              'Locale'          => 'de_DE.UTF-8',
                                                                              'TranslatorName'  => 'name',
                                                                              'TranslatorEmail' => 'email',
                                                                         )
                    )
                ),
            )
        );
    }

    /**
     * @dataProvider getFilterTestDataValid
     * @group Core
     */
    public function testFilterValid($translations)
    {
        $filter = new CoreTranslations();
        $result = $filter->isValid($translations);
        $this->assertTrue($result);
    }

    public function getFilterTestDataInvalid()
    {
        return array(
            array(
                array(
                    'General' => array(
                        'bla' => 'test text'
                    )
                ),
                CoreTranslations::ERRORSTATE_MINIMUMTRANSLATIONS
            ),
            array(
                array(
                    'General' => array_merge(array_fill(0, 251, 'test'), array(
                        'bla' => 'test text'
                    ))
                ),
                CoreTranslations::ERRORSTATE_LOCALEREQUIRED
            ),
            array(
                array(
                    'General' => array_merge(array_fill(0, 251, 'test'), array(
                        'Locale' => 'de_DE.UTF-8'
                    ))
                ),
                CoreTranslations::ERRORSTATE_TRANSLATORINFOREQUIRED
            ),
            array(
                array(
                    'General' => array_merge(array_fill(0, 251, 'test'), array(
                        'Locale' => 'de_DE.UTF-8',
                        'TranslatorName' => 'name',
                    ))
                ),
                CoreTranslations::ERRORSTATE_TRANSLATOREMAILREQUIRED
            ),
            array(
                array(
                    'General' => array_merge(array_fill(0, 251, 'test'), array(
                        'Locale' => 'de_DE.UTF-8',
                        'TranslatorName' => 'name',
                        'TranslatorEmail' => 'emails',
                        'LayoutDirection' => 'afd'
                    ))
                ),
                CoreTranslations::ERRORSTATE_LAYOUTDIRECTIONINVALID
            ),
            array(
                array(
                    'General' => array_merge(array_fill(0, 251, 'test'), array(
                        'Locale' => 'invalid',
                        'TranslatorName' => 'name',
                        'TranslatorEmail' => 'emails',
                        'LayoutDirection' => 'ltr'
                    ))
                ),
                CoreTranslations::ERRORSTATE_LOCALEINVALID
            ),
            array(
                array(
                    'General' => array_merge(array_fill(0, 251, 'test'), array(
                        'Locale' => 'xx_DE.UTF-8',
                        'TranslatorName' => 'name',
                        'TranslatorEmail' => 'emails',
                        'LayoutDirection' => 'ltr'
                    ))
                ),
                CoreTranslations::ERRORSTATE_LOCALEINVALIDLANGUAGE
            ),
            array(
                array(
                    'General' => array_merge(array_fill(0, 251, 'test'), array(
                        'Locale' => 'de_XX.UTF-8',
                        'TranslatorName' => 'name',
                        'TranslatorEmail' => 'emails',
                        'LayoutDirection' => 'ltr'
                    ))
                ),
                CoreTranslations::ERRORSTATE_LOCALEINVALIDCOUNTRY
            ),
        );
    }

    /**
     * @dataProvider getFilterTestDataInvalid
     * @group Core
     */
    public function testFilterInvalid($translations, $msg)
    {
        $filter = new CoreTranslations();
        $result = $filter->isValid($translations);
        $this->assertFalse($result);
        $this->assertEquals($msg, $filter->getMessage());
    }
}

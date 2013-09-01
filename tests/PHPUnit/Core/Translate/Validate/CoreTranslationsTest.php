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
     * @group Translate
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
                CoreTranslations::__ERRORSTATE_MINIMUMTRANSLATIONS__
            ),
            array(
                array(
                    'General' => array_merge(array_fill(0, 251, 'test'), array(
                        'bla' => 'test text'
                    ))
                ),
                CoreTranslations::__ERRORSTATE_LOCALEREQUIRED__
            ),
            array(
                array(
                    'General' => array_merge(array_fill(0, 251, 'test'), array(
                        'Locale' => 'de_DE.UTF-8'
                    ))
                ),
                CoreTranslations::__ERRORSTATE_TRANSLATORINFOREQUIRED__
            ),
            array(
                array(
                    'General' => array_merge(array_fill(0, 251, 'test'), array(
                        'Locale' => 'de_DE.UTF-8',
                        'TranslatorName' => 'name',
                    ))
                ),
                CoreTranslations::__ERRORSTATE_TRANSLATOREMAILREQUIRED__
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
                CoreTranslations::__ERRORSTATE_LAYOUTDIRECTIONINVALID__
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
                CoreTranslations::__ERRORSTATE_LOCALEINVALID__
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
                CoreTranslations::__ERRORSTATE_LOCALEINVALIDLANGUAGE__
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
                CoreTranslations::__ERRORSTATE_LOCALEINVALIDCOUNTRY__
            ),
        );
    }

    /**
     * @dataProvider getFilterTestDataInvalid
     * @group Core
     * @group Translate
     */
    public function testFilterInvalid($translations, $msg)
    {
        $filter = new CoreTranslations();
        $result = $filter->isValid($translations);
        $this->assertFalse($result);
        $this->assertEquals($msg, $filter->getError());
    }
}

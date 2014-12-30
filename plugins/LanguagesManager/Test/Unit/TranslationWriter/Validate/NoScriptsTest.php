<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\Test\Unit\TranslationWriter\Validate;

use Piwik\Plugins\LanguagesManager\TranslationWriter\Validate\NoScripts;

/**
 * @group LanguagesManager
 */
class NoScriptsTest extends \PHPUnit_Framework_TestCase
{
    public function getFilterTestDataValid()
    {
        return array(
            array(
                array(),
            ),
            array(
                array(
                    'test' => array()
                ),
            ),
            array(
                array(
                    'test' => array(
                        'key' => 'val%sue',
                        'test' => 'test'
                    )
                ),
            ),
        );
    }

    /**
     * @dataProvider getFilterTestDataValid
     * @group Core
     */
    public function testFilterValid($translations)
    {
        $filter = new NoScripts();
        $result = $filter->isValid($translations);
        $this->assertTrue($result);
    }

    public function getFilterTestDataInvalid()
    {
        return array(
            array(
                array(
                    'test' => array(
                        'test' => 'test text <script'
                    )
                ),
            ),
            array(
                array(
                    'empty' => array(
                        'test' => 't&uuml;sest'
                    ),
                    'test' => array(
                        'test' => 'bla <a href="javascript:alert();"> link </a>',
                        'empty' => '&tilde;',
                    )
                ),
            ),
            array(
                array(
                    'test' => array(
                        'test' => 'bla <a onload="alert(\'test\');">link</a>'
                    )
                ),
            ),
            array(
                array(
                    'test' => array(
                        'test' => 'no <img src="test" />'
                    )
                ),
            ),
            array(
                array(
                    'test' => array(
                        'test' => 'that will fail on document. or not?'
                    )
                ),
            ),
            array(
                array(
                    'test' => array(
                        'test' => 'bla <a background="yellow">link</a>'
                    )
                ),
            ),
        );
    }

    /**
     * @dataProvider getFilterTestDataInvalid
     * @group Core
     */
    public function testFilterInvalid($translations)
    {
        $filter = new NoScripts();
        $result = $filter->isValid($translations);
        $this->assertFalse($result);
    }
}

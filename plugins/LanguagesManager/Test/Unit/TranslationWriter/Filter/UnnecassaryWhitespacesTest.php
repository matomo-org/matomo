<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\Test\Unit\TranslationWriter\Filter;

use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\UnnecassaryWhitespaces;

/**
 * @group LanguagesManager
 */
class UnnecassaryWhitepsacesTest extends \PHPUnit_Framework_TestCase
{
    public function getFilterTestData()
    {
        return array(
            // empty stays empty - nothing to filter
            array(
                array(),
                array(),
                array(),
                array()
            ),
            // no entites - nothing to filter
            array(
                array(
                    'test' => array(
                        'key' => "val\n\n\r\n\nue",
                        'test' => 'test'
                    )
                ),
                array(
                    'test' => array(
                        'key' => "base val\n\nue",
                        'test' => 'test'
                    )
                ),
                array(
                    'test' => array(
                        'key' => "val\n\nue",
                        'test' => 'test'
                    )
                ),
                array(
                    'test' => array(
                        'key' => "val\n\n\r\n\nue",
                    )

                ),
            ),
            // entities needs to be decodded
            array(
                array(
                    'test' => array(
                        'test' => 'test                        palim'
                    )
                ),
                array(
                    'test' => array(
                        'test' => 'no line breaks'
                    )
                ),
                array(
                    'test' => array(
                        'test' => 'test palim'
                    )
                ),
                array(
                    'test' => array(
                        'test' => 'test                        palim'
                    )
                ),
            ),
            array(
                array(
                    'empty' => array(
                        'test' => "test\n\n\ntest"
                    ),
                ),
                array(
                    'empty' => array(
                        'test' => 'no line break'
                    ),
                ),
                array(
                    'empty' => array(
                        'test' => 'test test'
                    ),
                ),
                array(
                    'empty' => array(
                        'test' => "test\n\n\ntest"
                    ),
                ),
            ),
            array(
                array(
                    'empty' => array(
                        'test' => "test\n         \n\n      test"
                    ),
                ),
                array(
                    'empty' => array(
                        'test' => 'no line break'
                    ),
                ),
                array(
                    'empty' => array(
                        'test' => 'test test'
                    ),
                ),
                array(
                    'empty' => array(
                        'test' => "test\n         \n\n      test"
                    ),
                ),
            ),
            array(
                array(
                    'empty' => array(
                        'test' => "test\n         \n\n      test"
                    ),
                ),
                array(
                    'empty' => array(
                        'test' => "line\n break"
                    ),
                ),
                array(
                    'empty' => array(
                        'test' => "test\n\ntest"
                    ),
                ),
                array(
                    'empty' => array(
                        'test' => "test\n         \n\n      test"
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider getFilterTestData
     * @group Core
     */
    public function testFilter($translations, $baseTranslations, $expected, $filteredData)
    {
        $filter = new UnnecassaryWhitespaces($baseTranslations);
        $result = $filter->filter($translations);
        $this->assertEquals($expected, $result);
        $this->assertEquals($filteredData, $filter->getFilteredData());
    }
}

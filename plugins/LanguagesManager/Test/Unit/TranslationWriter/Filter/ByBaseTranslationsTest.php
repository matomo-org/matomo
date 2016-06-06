<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\Test\Unit\TranslationWriter\Filter;

use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\ByBaseTranslations;

/**
 * @group LanguagesManager
 */
class ByBaseTranslationsTest extends \PHPUnit_Framework_TestCase
{
    public function getFilterTestData()
    {
        return array(
            // empty stays empty
            array(
                array(),
                array(),
                array(),
                array()
            ),
            // empty plugin is removed
            array(
                array(
                    'test' => array()
                ),
                array(),
                array(),
                array(
                    'test' => array()
                ),
            ),
            // not existing values/plugins are removed
            array(
                array(
                    'test' => array(
                        'key' => 'value',
                        'test' => 'test'
                    )
                ),
                array(
                    'test' => array(
                        'key' => 'value',
                        'x'   => 'y'
                    )
                ),
                array(
                    'test' => array(
                        'key' => 'value',
                    )
                ),
                array(
                    'test' => array(
                        'test' => 'test',
                    )
                ),
            ),
            // no change if all exist
            array(
                array(
                    'test' => array(
                        'test' => 'test'
                    )
                ),
                array(
                    'test' => array(
                        'test' => 'test'
                    )
                ),
                array(
                    'test' => array(
                        'test' => 'test'
                    )
                ),
                array()
            ),
            // unavailable removed, others stay
            array(
                array(
                    'empty' => array(
                        'test' => 'test'
                    ),
                    'test' => array(
                        'test' => 'test',
                        'empty' => '     ',
                    )
                ),
                array(
                    'empty' => array(
                        'test' => 'test'
                    ),
                    'test' => array(
                        'test' => 'test',
                    )
                ),
                array(
                    'empty' => array(
                        'test' => 'test'
                    ),
                    'test' => array(
                        'test' => 'test'
                    )
                ),
                array(
                    'test' => array(
                        'empty' => '     ',
                    )
                )
            ),
            array(
                array(
                    'empty' => array(
                        'test' => 'test'
                    ),
                    'test' => array(
                        'test' => 'test',
                        'empty' => '     ',
                    )
                ),
                array(
                    'empty' => array(
                        'bla' => 'test'
                    ),
                    'test' => array(
                        'test' => 'test',
                    )
                ),
                array(
                    'test' => array(
                        'test' => 'test'
                    )
                ),
                array(
                    'empty' => array(
                        'test' => 'test'
                    ),
                    'test' => array(
                        'empty' => '     ',
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider getFilterTestData
     * @group Core
     */
    public function testFilter($translations, $baseTranslations, $expected, $filteredData)
    {
        $filter = new ByBaseTranslations($baseTranslations);
        $result = $filter->filter($translations);
        $this->assertEquals($expected, $result);
        $this->assertEquals($filteredData, $filter->getFilteredData());
    }
}

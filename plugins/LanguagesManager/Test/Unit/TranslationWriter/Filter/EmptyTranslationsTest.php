<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\Test\Unit\TranslationWriter\Filter;

use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\EmptyTranslations;

/**
 * @group LanguagesManager
 */
class EmptyTranslationsTest extends \PHPUnit_Framework_TestCase
{
    public function getFilterTestData()
    {
        return array(
            // empty stays empty
            array(
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
            ),
            // empty values/plugins are removed
            array(
                array(
                    'test' => array(
                        'empty' => '',
                        'whitespace' => '    '
                    )
                ),
                array(),
                array(
                    'test' => array(
                        'empty' => '',
                        'whitespace' => '    '
                    )
                ),
            ),
            // no change if no empty value
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
                array()
            ),
            // empty values are removed, others stay
            array(
                array(
                    'empty' => array(),
                    'test' => array(
                        'test' => 'test',
                        'empty' => '     ',
                    )
                ),
                array(
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
        );
    }

    /**
     * @dataProvider getFilterTestData
     * @group Core
     */
    public function testFilter($translations, $expected, $filteredData)
    {
        $filter = new EmptyTranslations();
        $result = $filter->filter($translations);
        $this->assertEquals($expected, $result);
        $this->assertEquals($filteredData, $filter->getFilteredData());
    }
}

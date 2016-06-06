<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\Test\Unit\TranslationWriter\Filter;

use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\ByParameterCount;

/**
 * @group LanguagesManager
 */
class ByParameterCountTest extends \PHPUnit_Framework_TestCase
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
            // empty plugin is removed
            array(
                array(
                    'test' => array()
                ),
                array(),
                array(),
                array(),
            ),
            // value with %s will be removed, as it isn't there in base
            array(
                array(
                    'test' => array(
                        'key' => 'val%sue',
                        'test' => 'test'
                    )
                ),
                array(
                    'test' => array(
                        'key' => 'value',
                    )
                ),
                array(),
                array(
                    'test' => array(
                        'key' => 'val%sue',
                    )
                ),
            ),
            // no change if placeholder count is the same
            array(
                array(
                    'test' => array(
                        'test' => 'te%sst'
                    )
                ),
                array(
                    'test' => array(
                        'test' => 'test%s'
                    )
                ),
                array(
                    'test' => array(
                        'test' => 'te%sst'
                    )
                ),
                array()
            ),
            // missing placeholder will be removed
            array(
                array(
                    'empty' => array(
                        'test' => 't%1$sest'
                    ),
                    'test' => array(
                        'test' => '%1$stest',
                        'empty' => '     ',
                    )
                ),
                array(
                    'empty' => array(
                        'test' => 'test%1$s'
                    ),
                    'test' => array(
                        'test' => '%1$stest%2$s',
                    )
                ),
                array(
                    'empty' => array(
                        'test' => 't%1$sest'
                    ),
                ),
                array(
                    'test' => array(
                        'test' => '%1$stest',
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
        $filter = new ByParameterCount($baseTranslations);
        $result = $filter->filter($translations);
        $message = sprintf("got %s but expected %s", var_export($result, true), var_export($expected, true));
        $this->assertEquals($expected, $result, $message);
        $this->assertEquals($filteredData, $filter->getFilteredData());
    }
}

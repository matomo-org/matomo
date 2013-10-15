<?php
use Piwik\Translate\Filter\ByParameterCount;

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class ByParameterCountTest extends PHPUnit_Framework_TestCase
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
                array(
                    'test' => array(
                        'test' => 'test',
                    )
                ),
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
                    'test' => array(
                        'empty' => '     ',
                    )
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
        $this->assertEquals($expected, $result);
        $this->assertEquals($filteredData, $filter->getFilteredData());
    }
}

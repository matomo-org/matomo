<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\Test\Unit\TranslationWriter\Filter;

use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\EncodedEntities;

/**
 * @group LanguagesManager
 */
class EncodedEntitiesTest extends \PHPUnit_Framework_TestCase
{
    public function getFilterTestData()
    {
        return array(
            // empty stays empty - nothing to filter
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
                array(
                    'test' => array()
                ),
                array(),
            ),
            // no entites - nothing to filter
            array(
                array(
                    'test' => array(
                        'key' => 'val%sue',
                        'test' => 'test'
                    )
                ),
                array(
                    'test' => array(
                        'key' => 'val%sue',
                        'test' => 'test'
                    )
                ),
                array(),
            ),
            // entities needs to be decodded
            array(
                array(
                    'test' => array(
                        'test' => 'te&amp;st'
                    )
                ),
                array(
                    'test' => array(
                        'test' => 'te&st'
                    )
                ),
                array(
                    'test' => array(
                        'test' => 'te&amp;st'
                    )
                ),
            ),
            array(
                array(
                    'empty' => array(
                        'test' => 't&uuml;sest'
                    ),
                    'test' => array(
                        'test' => '%1$stest',
                        'empty' => '&tilde;',
                    )
                ),
                array(
                    'empty' => array(
                        'test' => 'tüsest'
                    ),
                    'test' => array(
                        'test' => '%1$stest',
                        'empty' => '˜',
                    )
                ),
                array(
                    'empty' => array(
                        'test' => 't&uuml;sest'
                    ),
                    'test' => array(
                        'empty' => '&tilde;',
                    )
                ),
            ),
        );
    }

    /**
     * @dataProvider getFilterTestData
     * @group Core
     */
    public function testFilter($translations, $expected, $filteredData)
    {
        $filter = new EncodedEntities();
        $result = $filter->filter($translations);
        $this->assertEquals($expected, $result);
        $this->assertEquals($filteredData, $filter->getFilteredData());
    }
}

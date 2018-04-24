<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @backupGlobals enabled
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Intl;

use Piwik\Intl\Idn;

/**
 * @group Core
 */
class IdnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getDecodeIdnTestData
     */
    public function testDecodeIdn($expected, $domain)
    {
        $this->assertEquals($expected, Idn::decodeIdn($domain));
    }

    /**
     * Dataprovider
     * @return array
     */
    public function getDecodeIdnTestData()
    {
        return array(
            // INTL_IDNA_VARIANT_2003
            array('www.fußball.com', 'www.xn--fuball-cta.com'),

            // INTL_IDNA_VARIANT_UTS46
            array('пример.рф', 'xn--e1afmkfd.xn--p1ai'),
        );
    }
}

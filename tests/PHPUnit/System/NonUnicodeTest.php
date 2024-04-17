<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\SomeVisitsWithNonUnicodePageTitles;

/**
 * Tests that visits track & reports display correctly when non-unicode text is
 * used in URL query params of visits.
 *
 * @group NonUnicodeTest
 * @group Core
 */
class NonUnicodeTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array(
            'Actions.getSiteSearchKeywords',
            'Actions.getPageTitles',
            'Actions.getPageUrls',
            'Referrers.getWebsites',
        );

        return array(
            array($apiToCall, array('idSite'  => self::$fixture->idSite1,
                                    'date'    => self::$fixture->dateTime,
                                    'periods' => 'day'))
        );
    }

    public static function getOutputPrefix()
    {
        return 'NonUnicode';
    }
}

NonUnicodeTest::$fixture = new SomeVisitsWithNonUnicodePageTitles();

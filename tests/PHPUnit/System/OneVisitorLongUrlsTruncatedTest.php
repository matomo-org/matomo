<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\SomeVisitsWithLongUrls;

/**
 * Tests that filter_truncate works recursively in Page URLs report AND in the case there are 2 different data Keywords -> search engine
 *
 * @group OneVisitorLongUrlsTruncatedTest
 * @group Core
 */
class OneVisitorLongUrlsTruncatedTest extends SystemTestCase
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
            'Referrers.getKeywords',
            'Actions.getPageUrls',

            // Specifically testing getPlugin filter_truncate works
            'DevicePlugins.getPlugin');

        return array(
            array($apiToCall, array('idSite'                 => self::$fixture->idSite,
                                    'date'                   => self::$fixture->dateTime,
                                    'language'               => 'fr',
                                    'otherRequestParameters' => array('expanded' => 1, 'filter_truncate' => 2)))
        );
    }

    public static function getOutputPrefix()
    {
        return 'OneVisitor_LongUrlsTruncated';
    }
}

OneVisitorLongUrlsTruncatedTest::$fixture = new SomeVisitsWithLongUrls();

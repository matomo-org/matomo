<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\FewVisitsAnonymizedFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group PrivacyManager
 * @group AnonymizationTest
 * @group Plugins
 */
class AnonymizationTest extends SystemTestCase
{
    /**
     * @var FewVisitsAnonymizedFixture
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $params['xmlFieldsToRemove'] = [];
        $this->runApiTests($api, $params);
    }

    public function test_orderIdAnonymized()
    {
        $idOrder = Db::fetchOne('SELECT idorder FROM ' . Common::prefixTable('log_conversion'));
        $this->assertSame(40, strlen($idOrder));
        $this->assertTrue(ctype_xdigit($idOrder));
    }

    public function getApiForTesting()
    {
        $apiToTest = [];
        $apiToTest[] = [['Live.getLastVisitsDetails', 'Referrers.getAll'],
            [
                'idSite'     => self::$fixture->idSite,
                'date'       => self::$fixture->dateTime,
                'periods'    => ['year'],
                'otherRequestParameters' => ['doNotFetchActions' => '1', 'filter_limit' => '-1'],
                'testSuffix' => 'userIdAnonymized'
            ]
        ];

        return $apiToTest;
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

AnonymizationTest::$fixture = new FewVisitsAnonymizedFixture();

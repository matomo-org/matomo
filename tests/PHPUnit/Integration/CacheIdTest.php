<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\CacheId;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Cache
 * @group CacheId
 */
class CacheIdTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
    }

    public function test_languageAware_shouldAppendTheLoadedLanguage()
    {
        $result = CacheId::languageAware('myrandomkey');

        $this->assertEquals('myrandomkey-en', $result);
    }

    public function test_pluginAware_shouldAppendLoadedPluginsAndLanguage()
    {
        $result = CacheId::pluginAware('myrandomkey');

        $parts = explode('-', $result);

        $this->assertCount(3, $parts);
        $this->assertEquals('myrandomkey', $parts[0]);
        $this->assertEquals(32, strlen($parts[1]), $parts[1] . ' is not a MD5 hash');
        $this->assertEquals('en', $parts[2]);
    }

    public function test_siteAware_shouldAppendIdSitesPassed()
    {
        $result = CacheId::siteAware('key', [1,2,3]);
        $this->assertEquals('key-1_2_3', $result);

        $result = CacheId::siteAware('key', [1,2,3,4,5,6,7,9]);
        $this->assertEquals('key-f791852e88bb2a1f130f37b4a9e2c351', $result);
    }

    /**
     * @dataProvider getTestDataForSiteAware
     */
    public function test_siteAware_shouldAppendIdSiteQueryParams_IfArgumentIsNull($getParams, $postParams, $expected)
    {
        $_GET = $getParams;
        $_POST = $postParams;

        $result = CacheId::siteAware('key');
        $this->assertEquals($expected, $result);
    }

    public function getTestDataForSiteAware()
    {
        return [
            [
                ['idSite' => '1'],
                [],
                'key-1',
            ],
            [
                ['idSite' => '0,1,2'],
                [],
                'key-0_1_2',
            ],
            [
                ['idSites' => '0,4,3,2'],
                [],
                'key-0_2_3_4',
            ],
            [
                ['idSite' => 'all'],
                [],
                'key-0',
            ],
            [
                ['idSite' => '3,4', 'idSites' => '4,5,6'],
                [],
                'key-3_4-4_5_6',
            ],
            [
                ['idSite' => '3,4,5,6,7,8', 'idSites' => '9,10,11,12,19,14,15'],
                [],
                'key-09c89807ba10937c5ced44af9d9d49e8-8bec4c3209c94166186190a26a2920cb',
            ],
            [
                [],
                [],
                'key',
            ],
            [
                ['idSite' => '3,4,5', 'idsite' => '9,13,15'],
                [],
                'key-3_4_5-9_13_15',
            ],
            [
                ['idSite' => '3,4,5', 'idSites' => '1', 'idsite' => '9,13,15'],
                [],
                'key-3_4_5-1-9_13_15',
            ],
            [
                ['idSite' => '1,2'],
                ['idSite' => '4,5'],
                'key-1_2_4_5',
            ],
            [
                ['idSites' => '1,2'],
                ['idSite' => '4,5'],
                'key-4_5-1_2',
            ],
            [
                ['idSite' => '1,2', 'idSites' => '9,9', 'idsite' => '12,13'],
                ['idSite' => '4,5', 'idSites' => '9,8', 'idsite' => '14,15'],
                'key-1_2_4_5-8_9-12_13_14_15',
            ],

            // must support $_GET/$_POST values being arrays and not strings
            [
                ['idSite' => ['1', '2'], 'idSites' => ['9', '9'], 'idsite' => ['12', '13']],
                ['idSite' => ['4', '5'], 'idSites' => ['9', '8'], 'idsite' => ['14', '15']],
                'key-1_2_4_5-8_9-12_13_14_15',
            ],
        ];
    }
}

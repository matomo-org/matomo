<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace PHPUnit\Integration\Tracker\Config;

use Piwik\Config;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Config\ThirdPartyCookies;

class ThirdPartyCookiesTest extends IntegrationTestCase
{
    /**
     * @dataProvider dataGetValueNoIdSite
     * @param bool $instanceWideSettingValue
     * @param bool|null $siteSpecificSettingValue
     * @param bool $expectedValue
     * @return void
     */
    public function testGetValueReturnsCorrectValueWhenNoIdSiteProvided(
        bool $instanceWideSettingValue,
        ?bool $siteSpecificSettingValue,
        bool $expectedValue
    ): void {

        Config::getInstance()->Tracker['use_third_party_id_cookie'] = $instanceWideSettingValue;

        if ($siteSpecificSettingValue !== null) {
            Config::getInstance()->Tracker_1['use_third_party_id_cookie'] = $siteSpecificSettingValue;
        }

        $this->assertEquals($expectedValue, ThirdPartyCookies::getInstance()->getValue());
    }

    /**
     * @return iterable<string, array{bool, ?bool, bool}>
     */
    public function dataGetValueNoIdSite(): iterable
    {
        yield 'instance wide set to 0, site specific set to 0' => [
            false,
            false,
            false,
        ];
        yield 'instance wide set to 1, site specific set to 0' => [
            true,
            false,
            true,
        ];
        yield 'instance wide set to 0, site specific set to 1' => [
            false,
            true,
            false,
        ];
        yield 'instance wide set to 1, site specific set to 1' => [
            true,
            true,
            true,
        ];
        yield 'instance wide set to 1, site specific set to null' => [
            true,
            null,
            true,
        ];
    }

    /**
     * @dataProvider dataGetValueIdSite
     * @param int $idSite
     * @param bool $instanceWideSettingValue
     * @param bool|null $siteSpecificSettingValue
     * @param bool $expectedValue
     * @return void
     */
    public function testGetValueReturnsCorrectValueWhenIdSiteProvided(
        int $idSite,
        bool $instanceWideSettingValue,
        ?bool $siteSpecificSettingValue,
        bool $expectedValue
    ): void {

        Config::getInstance()->Tracker['use_third_party_id_cookie'] = $instanceWideSettingValue;
        Config::getInstance()->Tracker_1['use_third_party_id_cookie'] = $siteSpecificSettingValue;

        $this->assertEquals($expectedValue, ThirdPartyCookies::getInstance($idSite)->getValue());
    }

    /**
     * @return iterable<string, array{int, bool, ?bool, bool}>
     */
    public function dataGetValueIdSite(): iterable
    {
        yield 'instance wide set to 1, site specific set to 0' => [
            1,
            true,
            false,
            false,
        ];
        yield 'instance wide set to 1, site specific set to 1' => [
            1,
            true,
            true,
            true,
        ];
        yield 'instance wide set to 0, site specific set to null' => [
            1,
            false,
            null,
            false,
        ];
        yield 'instance wide set to 0, site specific set to 1' => [
            1,
            false,
            true,
            true,
        ];
        yield 'instance wide set to 0, site specific set to 1, different site selected' => [
            2,
            false,
            true,
            false,
        ];
    }

    /**
     * @dataProvider dataIsCompliant
     * @param string $policy
     * @param bool $thirdPartyCookieSetting
     * @param bool $expected
     * @return void
     */
    public function testIsCompliant(
        string $policy,
        bool $thirdPartyCookieSetting,
        bool $expected
    ): void {
        Config::getInstance()->Tracker['use_third_party_id_cookie'] = $thirdPartyCookieSetting;

        $this->assertEquals($expected, ThirdPartyCookies::isCompliant($policy));
    }

    /**
     * @return iterable<string, array{string, bool, bool}>
     */
    public function dataIsCompliant(): iterable
    {
        yield 'CNIL, third party cookie on' => [
            CnilPolicy::class,
            true,
            false,
        ];
        yield 'CNIL, third party cookie off' => [
            CnilPolicy::class,
            false,
            true,
        ];
    }
}

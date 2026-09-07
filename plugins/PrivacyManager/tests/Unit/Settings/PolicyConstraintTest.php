<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Unit\Settings;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\PrivacyManager\ReferrerAnonymizer;
use Piwik\Plugins\PrivacyManager\Settings\IPAnonymisation;
use Piwik\Plugins\PrivacyManager\Settings\IpAddressMaskLength;
use Piwik\Plugins\PrivacyManager\Settings\ReferrerAnonymisation;
use Piwik\Plugins\PrivacyManager\Settings\ReportRetention;
use Piwik\Policy\CnilPolicy;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;

/**
 * How a compliance policy constrains the privacy settings it controls. Only settings whose
 * requirement is the single compliant value may be locked on their settings screen; the ones the
 * policy merely bounds have to stay editable, so that a user can still choose a stricter value.
 *
 * @group PrivacyManager
 * @group Plugins
 */
class PolicyConstraintTest extends TestCase
{
    public function testRetentionPeriodIsBoundedFromAbove(): void
    {
        $this->assertSame(
            PolicyComparisonInterface::POLICY_CONSTRAINT_MAX,
            ReportRetention::getPolicyConstraintType(CnilPolicy::class)
        );
    }

    public function testIpAddressMaskLengthIsBoundedFromBelow(): void
    {
        $this->assertSame(
            PolicyComparisonInterface::POLICY_CONSTRAINT_MIN,
            IpAddressMaskLength::getPolicyConstraintType(CnilPolicy::class)
        );
    }

    public function testReferrerAnonymisationIsBoundedFromBelow(): void
    {
        $this->assertSame(
            PolicyComparisonInterface::POLICY_CONSTRAINT_MIN,
            ReferrerAnonymisation::getPolicyConstraintType(CnilPolicy::class)
        );
    }

    public function testSettingsWithASingleCompliantValueAreExact(): void
    {
        $this->assertSame(
            PolicyComparisonInterface::POLICY_CONSTRAINT_EXACT,
            IPAnonymisation::getPolicyConstraintType(CnilPolicy::class)
        );
    }

    /**
     * @dataProvider getRetentionPeriods
     */
    public function testRetentionPeriodCompliance(int $days, bool $expected): void
    {
        $this->assertSame($expected, ReportRetention::isValueCompliantWithPolicy($days, CnilPolicy::class));
    }

    public function getRetentionPeriods(): array
    {
        return [
            'keeping data for less than the maximum stays compliant' => [180, true],
            'keeping data for exactly the maximum is compliant' => [759, true],
            'keeping data for longer than the maximum is not' => [760, false],
        ];
    }

    /**
     * @dataProvider getMaskLengths
     */
    public function testIpAddressMaskLengthCompliance($maskLength, bool $expected): void
    {
        $this->assertSame($expected, IpAddressMaskLength::isValueCompliantWithPolicy($maskLength, CnilPolicy::class));
    }

    public function getMaskLengths(): array
    {
        return [
            'masking fewer bytes than required is not compliant' => [1, false],
            'masking exactly the required bytes is compliant' => [2, true],
            'masking more bytes than required stays compliant' => [3, true],
            // selectable values reach this as strings, from option lists and from requests
            'the required value as a string is compliant' => ['2', true],
            'a lower value as a string is not compliant' => ['1', false],
        ];
    }

    /**
     * @dataProvider getReferrerAnonymisations
     */
    public function testReferrerAnonymisationCompliance(string $value, bool $expected): void
    {
        $this->assertSame($expected, ReferrerAnonymisation::isValueCompliantWithPolicy($value, CnilPolicy::class));
    }

    public function getReferrerAnonymisations(): array
    {
        return [
            'keeping the whole referrer is not compliant' => [ReferrerAnonymizer::EXCLUDE_NONE, false],
            'stripping only the query is not enough' => [ReferrerAnonymizer::EXCLUDE_QUERY, false],
            'stripping the path is exactly what is required' => [ReferrerAnonymizer::EXCLUDE_PATH, true],
            'stripping everything stays compliant' => [ReferrerAnonymizer::EXCLUDE_ALL, true],
            'an unknown value is not compliant' => ['something_else', false],
        ];
    }

    public function testValuesOfSettingsAPolicyDoesNotControlAreAlwaysCompliant(): void
    {
        $this->assertTrue(ReportRetention::isValueCompliantWithPolicy(100000, 'Some\\Other\\Policy'));
    }
}

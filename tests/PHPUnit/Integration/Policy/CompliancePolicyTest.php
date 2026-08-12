<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Policy;

use Piwik\Tests\Framework\Mock\Policy\TestPolicy;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Exercises the whole-policy active flag, which fans out to the per-setting
 * enforcement state. {@link \Piwik\Policy\CompliancePolicy::setActiveStatus()}
 * enumerates the policy-controlled settings via the plugin manager, so a
 * database is required and this case cannot live in the unit suite.
 *
 * @group Core
 */
class CompliancePolicyTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        TestPolicy::reset();
    }

    /**
     * @dataProvider possibleStatesForPolicyActive
     */
    public function testSetActiveStatusInstanceLevel(
        $idSite,
        $newActiveState,
        $currentInstanceState,
        $currentSiteState,
        $expectedInstanceState,
        $expectedSiteState
    ): void {
        TestPolicy::setState($currentInstanceState, $currentSiteState ? 99 : false);
        TestPolicy::setActiveStatus($idSite, $newActiveState);
        $this->assertSame(TestPolicy::isActive(null), $expectedInstanceState, "Instance status $expectedInstanceState is incorrect");
        $this->assertSame(TestPolicy::isActive(99), $expectedSiteState, "Site status $expectedSiteState is incorrect");
    }

    public function possibleStatesForPolicyActive()
    {
        /*
            [
                idSite,
                newActiveState,
                currentInstanceState,
                currentSiteState,
                expectedInstanceState,
                expectedSiteState
            ]
         */
        yield [null, true, true, true, true, true];
        yield [null, true, true, false, true, true];
        yield [null, true, false, true, true, true];
        yield [null, true, false, false, true, true];
        yield [null, false, true, true, false, true];
        yield [null, false, true, false, false, false];
        yield [null, false, false, true, false, true];
        yield [null, false, false, false, false, false];
        yield [99, true, true, true, true, true];
        yield [99, true, true, false, true, true];
        yield [99, true, false, true, false, true];
        yield [99, true, false, false, false, true];
        yield [99, false, true, true, false, false];
        yield [99, false, true, false, false, false];
        yield [99, false, false, true, false, false];
        yield [99, false, false, false, false, false];
    }
}

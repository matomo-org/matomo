<?php

namespace Piwik\Tests\Unit\Policy;

use PHPUnit\Framework\TestCase;
use Piwik\Tests\Framework\Mock\Policy\TestPolicy;

class CompliancePolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestPolicy::reset();
    }

    public function testGetDetailsReturnsExpectedMetadata(): void
    {
        $details = TestPolicy::getDetails();

        $this->assertSame('test_policy_v1', $details['id']);
        $this->assertSame('Test Policy', $details['title']);
        $this->assertSame('Test policy description', $details['description']);
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

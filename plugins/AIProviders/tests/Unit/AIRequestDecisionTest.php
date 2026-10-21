<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\tests\Unit;

use Piwik\Plugins\AIProviders\AIRequestDecision;
use PHPUnit\Framework\TestCase;

/**
 * @group AIProviders
 */
class AIRequestDecisionTest extends TestCase
{
    public function testReportUsageFillsTheFiguresOfAnAllowedDecision(): void
    {
        $decision = new AIRequestDecision();
        $decision->reportUsage(20, 25, 5);

        $this->assertTrue($decision->isAllowed());
        $this->assertSame(20, $decision->getUsed());
        $this->assertSame(25, $decision->getLimit());
        $this->assertSame(5, $decision->getRemaining());
    }

    public function testReportUsageLeavesADeniedDecisionUntouched(): void
    {
        $decision = new AIRequestDecision();
        $decision->deny('limit_reached', 'Too many', 25, 25, 0);
        $decision->reportUsage(1, 100, 99);

        $this->assertFalse($decision->isAllowed());
        $this->assertSame(25, $decision->getUsed());
        $this->assertSame(25, $decision->getLimit());
        $this->assertSame(0, $decision->getRemaining());
    }

    public function testDenyAfterReportUsageReplacesTheFigures(): void
    {
        $decision = new AIRequestDecision();
        $decision->reportUsage(20, 25, 5);
        $decision->deny('limit_reached', null, 25, 25, 0);

        $this->assertSame(25, $decision->getUsed());
        $this->assertSame(0, $decision->getRemaining());
    }

    public function testReportUsageKeepsTheStrictestOfSeveralReports(): void
    {
        $decision = new AIRequestDecision();
        $decision->reportUsage(20, 25, 5);
        $decision->reportUsage(10, 100, 90);
        $this->assertSame(25, $decision->getLimit());

        $decision->reportUsage(24, 25, 1);
        $this->assertSame(1, $decision->getRemaining());
        $this->assertSame(24, $decision->getUsed());
    }

    public function testReportUsageWithoutRemainingNeverLoosensAnExistingReport(): void
    {
        $decision = new AIRequestDecision();
        $decision->reportUsage(20, 25, 5);
        $decision->reportUsage(null, null, null);

        $this->assertSame(20, $decision->getUsed());
        $this->assertSame(25, $decision->getLimit());
        $this->assertSame(5, $decision->getRemaining());
    }
}

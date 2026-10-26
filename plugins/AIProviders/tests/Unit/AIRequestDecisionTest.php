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
use Piwik\Plugins\AIProviders\AIUsageAction;
use PHPUnit\Framework\TestCase;

/**
 * @group AIProviders
 * @group Plugins
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

    public function testReportUsageWithRemainingReplacesAnUnlimitedReport(): void
    {
        $decision = new AIRequestDecision();
        $decision->reportUsage(3, null, null);
        $decision->reportUsage(20, 25, 5);

        $this->assertSame(20, $decision->getUsed());
        $this->assertSame(25, $decision->getLimit());
        $this->assertSame(5, $decision->getRemaining());
    }

    public function testOnATieTheLaterReportWins(): void
    {
        $action = new AIUsageAction('Buy more credits', 'index.php?module=Billing');
        $decision = new AIRequestDecision();
        $decision->reportUsage(20, 25, 5, 'Earlier', new AIUsageAction('Upgrade', 'index.php?module=Plans'));
        $decision->reportUsage(95, 100, 5, 'Later', $action);

        $this->assertSame(95, $decision->getUsed());
        $this->assertSame(100, $decision->getLimit());
        $this->assertSame(5, $decision->getRemaining());
        $this->assertSame('Later', $decision->getMessage());
        $this->assertSame($action, $decision->getAction());
    }

    public function testAnEmptyReportNeverWipesOneWithFigures(): void
    {
        $decision = new AIRequestDecision();
        $decision->reportUsage(20, 25, null);
        $decision->reportUsage(null, null, null);

        $this->assertSame(20, $decision->getUsed());
        $this->assertSame(25, $decision->getLimit());
    }

    public function testReportUsageWithoutRemainingNeverLoosensAnExistingReport(): void
    {
        $decision = new AIRequestDecision();
        $decision->reportUsage(20, 25, 5);
        $decision->reportUsage(3, null, null);

        $this->assertSame(20, $decision->getUsed());
        $this->assertSame(25, $decision->getLimit());
        $this->assertSame(5, $decision->getRemaining());
    }

    public function testReportUsageCarriesItsMessageAndAction(): void
    {
        $action = new AIUsageAction('Buy more credits', 'index.php?module=Billing&action=index');
        $decision = new AIRequestDecision();
        $decision->reportUsage(80, 100, 20, 'Renews in November.', $action);

        $this->assertTrue($decision->isAllowed());
        $this->assertSame('Renews in November.', $decision->getMessage());
        $this->assertSame($action, $decision->getAction());
    }

    public function testTheMessageAndActionTravelWithTheStrictestReport(): void
    {
        $decision = new AIRequestDecision();
        $decision->reportUsage(10, 100, 90, 'Loose', new AIUsageAction('Loose', 'index.php'));
        $decision->reportUsage(24, 25, 1, 'Strict');
        $decision->reportUsage(50, 100, 50, 'Ignored', new AIUsageAction('Ignored', 'index.php'));

        $this->assertSame('Strict', $decision->getMessage());
        $this->assertNull($decision->getAction());
    }

    public function testAStricterReportWithoutAMessageClearsTheEarlierMessage(): void
    {
        $decision = new AIRequestDecision();
        $decision->reportUsage(10, 100, 90, 'Renews in November.');
        $decision->reportUsage(24, 25, 1);

        $this->assertSame(1, $decision->getRemaining());
        $this->assertNull($decision->getMessage());
    }

    public function testDenyCarriesItsOwnAction(): void
    {
        $action = new AIUsageAction('Buy more credits', 'index.php?module=Billing&action=index');
        $decision = new AIRequestDecision();
        $decision->reportUsage(20, 25, 5, 'Renews soon', new AIUsageAction('Upgrade', 'index.php?module=Plans'));
        $decision->deny('limit_reached', 'Used up', 25, 25, 0, $action);

        $this->assertFalse($decision->isAllowed());
        $this->assertSame('Used up', $decision->getMessage());
        $this->assertSame($action, $decision->getAction());
    }

    public function testDenyWithoutAnActionDropsTheActionOfAnEarlierReport(): void
    {
        $decision = new AIRequestDecision();
        $decision->reportUsage(20, 25, 5, 'Renews soon', new AIUsageAction('Buy more', 'index.php'));
        $decision->deny('limit_reached', 'Used up', 25, 25, 0);

        $this->assertSame('Used up', $decision->getMessage());
        $this->assertNull($decision->getAction());
    }

    public function testDenyWithoutAMessageDropsTheMessageOfAnEarlierReport(): void
    {
        $decision = new AIRequestDecision();
        $decision->reportUsage(20, 25, 5, 'Renews soon');
        $decision->deny('limit_reached');

        $this->assertNull($decision->getMessage());
    }

    public function testALaterDenialDoesNotReplaceTheFirstDenialsAction(): void
    {
        $decision = new AIRequestDecision();
        $decision->deny('disabled');
        $decision->deny('limit_reached', 'Used up', 25, 25, 0, new AIUsageAction('Buy more', 'index.php'));

        $this->assertSame('disabled', $decision->getReason());
        $this->assertNull($decision->getAction());
    }
}

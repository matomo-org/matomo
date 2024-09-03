<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Commands;

use Piwik\Plugins\CustomDimensions\Commands\AddCustomDimension;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomDimensions
 * @group CustomDimensionsTest
 * @group Plugins
 * @group Plugins
 */
class AddCustomDimensionTest extends IntegrationTestCase
{
    public function testExecuteShouldThrowExceptionIfArgumentIsMissing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The specified scope is invalid. Use either');

        $this->executeCommand(null, null);
    }

    public function testExecuteShouldThrowExceptionIfScopeIsInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The specified scope is invalid. Use either "--scope=visit" or "--scope=action"');

        $this->executeCommand('invalidscope', null);
    }

    public function testExecuteShouldThrowExceptionIfCountIsNotANumber()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "count" must be a number');

        $this->executeCommand(CustomDimensions::SCOPE_VISIT, '545fddfd');
    }

    public function testExecuteShouldThrowExceptionIfCountIsLessThanONe()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "count" must be at least one');

        $this->executeCommand(CustomDimensions::SCOPE_VISIT, '0');
    }

    public function testExecuteShouldThrowExceptionIfUserCancelsConfirmation()
    {
        $result = $this->executeCommand(CustomDimensions::SCOPE_VISIT, $count = 5, false);
        $this->assertStringEndsWith('Are you sure you want to perform this action? (y/N)', $result);
    }

    public function testExecuteShouldAddSpecifiedCount()
    {
        $logVisit = new LogTable(CustomDimensions::SCOPE_VISIT);
        $this->assertSame(range(1, 5), $logVisit->getInstalledIndexes());

        $logConversion = new LogTable(CustomDimensions::SCOPE_CONVERSION);
        $this->assertSame(range(1, 5), $logConversion->getInstalledIndexes());

        $logAction = new LogTable(CustomDimensions::SCOPE_ACTION);
        $this->assertSame(range(1, 5), $logAction->getInstalledIndexes());

        $result = $this->executeCommand(CustomDimensions::SCOPE_ACTION, $count = 3);

        self::assertStringContainsString('Adding 3 Custom Dimension(s) in scope action.', $result);
        self::assertStringContainsString('Are you sure you want to perform this action?', $result);
        self::assertStringContainsString('Starting to add Custom Dimension(s)', $result);
        self::assertStringContainsString('Your Matomo is now configured for up to 8 Custom Dimensions in scope action.', $result);

        $logVisit = new LogTable(CustomDimensions::SCOPE_VISIT);
        $this->assertSame(range(1, 5), $logVisit->getInstalledIndexes());

        $logConversion = new LogTable(CustomDimensions::SCOPE_CONVERSION);
        $this->assertSame(range(1, 5), $logConversion->getInstalledIndexes());

        $logAction = new LogTable(CustomDimensions::SCOPE_ACTION);
        $this->assertSame(range(1, 8), $logAction->getInstalledIndexes());
    }

    public function testExecuteShouldAddSpecifiedCountIfScopeIsVisitShouldAlsoUpdateConversion()
    {
        $logVisit = new LogTable(CustomDimensions::SCOPE_VISIT);
        $this->assertSame(range(1, 5), $logVisit->getInstalledIndexes());

        $logConversion = new LogTable(CustomDimensions::SCOPE_CONVERSION);
        $this->assertSame(range(1, 5), $logConversion->getInstalledIndexes());

        $logAction = new LogTable(CustomDimensions::SCOPE_ACTION);
        $this->assertSame(range(1, 8), $logAction->getInstalledIndexes());

        $result = $this->executeCommand(CustomDimensions::SCOPE_VISIT, $count = 2);

        self::assertStringContainsString('Adding 2 Custom Dimension(s) in scope visit.', $result);
        self::assertStringContainsString('Are you sure you want to perform this action?', $result);
        self::assertStringContainsString('Starting to add Custom Dimension(s)', $result);
        self::assertStringContainsString('Your Matomo is now configured for up to 7 Custom Dimensions in scope visit.', $result);

        $logVisit = new LogTable(CustomDimensions::SCOPE_VISIT);
        $this->assertSame(range(1, 7), $logVisit->getInstalledIndexes());

        $logConversion = new LogTable(CustomDimensions::SCOPE_CONVERSION);
        $this->assertSame(range(1, 7), $logConversion->getInstalledIndexes());

        $logAction = new LogTable(CustomDimensions::SCOPE_ACTION);
        $this->assertSame(range(1, 8), $logAction->getInstalledIndexes());
    }

    /**
     * @param string|null $scope
     * @param int|null $count
     * @param bool  $confirm
     *
     * @return string
     */
    private function executeCommand($scope, $count, $confirm = true)
    {
        $addCustomDimension = new AddCustomDimension();

        $application = new Application();
        $application->add($addCustomDimension);

        $commandTester = new CommandTester($addCustomDimension);
        $commandTester->setInputs([($confirm ? 'yes' : 'no') . "\n"]);

        $params = [];
        if (!is_null($scope)) {
            $params['--scope'] = $scope;
        }

        if (!is_null($count)) {
            $params['--count'] = $count;
        }

        $params['command'] = $addCustomDimension->getName();
        $commandTester->execute($params);
        return $commandTester->getDisplay();
    }
}

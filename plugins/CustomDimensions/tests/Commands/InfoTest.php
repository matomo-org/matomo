<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Commands;

use Piwik\Plugins\CustomDimensions\Commands\Info;
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
class InfoTest extends IntegrationTestCase
{
    public function testExecuteShouldOutputInfoSuccessIfEverythingIsOk()
    {
        $output = $this->executeCommand();

        self::assertStringContainsString(
            './console customdimensions:add-custom-dimension --scope=visit"
Installed indexes are:
1 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=visit --index=1
2 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=visit --index=2
3 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=visit --index=3
4 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=visit --index=4
5 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=visit --index=5

5 Custom Dimensions available in scope "action"
To add a Custom Dimension execute "./console customdimensions:add-custom-dimension --scope=action"
Installed indexes are:
1 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=action --index=1
2 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=action --index=2
3 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=action --index=3
4 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=action --index=4
5 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=action --index=5

5 Custom Dimensions available in scope "conversion"
Custom Dimensions are automatically added via the scope "visit" and cannot be added manually
Installed indexes are:
1 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=conversion --index=1
2 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=conversion --index=2
3 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=conversion --index=3
4 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=conversion --index=4
5 to remove this Custom Dimension execute ./console customdimensions:remove-custom-dimension --scope=conversion --index=5',
            $output
        );
    }

    public function testExecuteShouldOutputErrorMessageIfColumnsDoNotMatch()
    {
        $model = new LogTable(CustomDimensions::SCOPE_CONVERSION);
        $model->removeCustomDimension(5);

        self::assertStringContainsString('We found an error, Custom Dimensions in scope "conversion" are not correctly installed. Execute the following command to repair it:
./console customdimensions:add-custom-dimension --scope=conversion --count=1', $this->executeCommand());
    }

    private function executeCommand()
    {
        $infoCmd = new Info();

        $application = new Application();
        $application->add($infoCmd);
        $commandTester = new CommandTester($infoCmd);

        $commandTester->execute(['command' => $infoCmd->getName()]);
        return $commandTester->getDisplay();
    }
}

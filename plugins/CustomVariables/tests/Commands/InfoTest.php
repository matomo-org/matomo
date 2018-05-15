<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomVariables\tests\Commands;

use Piwik\Plugins\CustomVariables\Commands\Info;
use Piwik\Plugins\CustomVariables\Model;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomVariables
 * @group CustomVariablesTest
 * @group Plugins
 * @group Plugins
 */
class InfoTest extends IntegrationTestCase
{
    public function testExecute_ShouldOutputInfoSuccess_IfEverythingIsOk()
    {
        $this->assertContains('Your Piwik is configured for 5 custom variables.', $this->executeCommand());
    }

    public function testExecute_ShouldOutputErrorMessage_IfColumnsDoNotMatch()
    {
        $model = new Model(Model::SCOPE_PAGE);
        $model->removeCustomVariable();

        $this->assertContains('There is a problem with your custom variables configuration', $this->executeCommand());
    }

    private function executeCommand()
    {
        $infoCmd = new Info();

        $application = new Application();
        $application->add($infoCmd);
        $commandTester = new CommandTester($infoCmd);

        $commandTester->execute(array('command' => $infoCmd->getName()));
        $result = $commandTester->getDisplay();

        return $result;
    }
}

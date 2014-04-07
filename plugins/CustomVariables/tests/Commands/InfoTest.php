<?php
/**
 * Piwik - Open source web analytics
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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @group CustomVariables
 * @group CustomVariablesTest
 * @group Database
 * @group Plugins
 */
class InfoTest extends \DatabaseTestCase
{
    public function testExecute_ShouldOutputInfoSuccess_IfEverythingIsOk()
    {
        $this->assertEquals('Your Piwik is configured for 5 custom variables.', $this->executeCommand());
    }

    public function testExecute_ShouldOutputErrorMessage_IfColumnsDoNotMatch()
    {
        $model = new Model(Model::SCOPE_PAGE);
        $model->removeCustomVariable();

        $this->assertEquals('There is a problem with your custom variables configuration', $this->executeCommand());
    }

    private function executeCommand()
    {
        $application = new Application();
        $application->add(new Info());

        $command = $application->find('customvariables:info');
        $commandTester = new CommandTester($command);

        $commandTester->execute(array('command' => $command->getName()));
        $result = $commandTester->getDisplay();

        return $result;
    }
}

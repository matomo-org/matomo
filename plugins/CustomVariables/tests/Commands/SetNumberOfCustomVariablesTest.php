<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomVariables\tests\Commands;

use Piwik\Plugins\CustomVariables\Commands\SetNumberOfCustomVariables;
use Piwik\Plugins\CustomVariables\CustomVariables;
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
class SetNumberOfCustomVariablesTest extends \DatabaseTestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage  Not enough arguments
     */
    public function testExecute_ShouldThrowException_IfArgumentIsMissing()
    {
        $this->executeCommand(array(), true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  The number of available custom variables has to be a number
     */
    public function testExecute_ShouldThrowException_HasToBeANumber()
    {
        $this->executeCommand(array('maxCustomVars' => 'a'), true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  There has to be at least two custom variables
     */
    public function testExecute_ShouldThrowException_Minimum2CustomVarsRequired()
    {
        $this->executeCommand(array('maxCustomVars' => 1), true);
    }

    public function testExecute_ShouldThrowException_IfUserCancelsConfirmation()
    {
        $result = $this->executeCommand(array('maxCustomVars' => 4), false);
        $this->assertStringEndsWith('Are you sure you want to perform these actions? (y/N)', $result);
    }

    public function testExecute_ShouldDoNothingIfExpectedResult_IsAlreadyTheCase()
    {
        $result = $this->executeCommand(array('maxCustomVars' => 5), true);

        $this->assertContains('Your Piwik is already configured for 5 custom variables', $result);
    }

    public function testExecute_ShouldRemoveMaxCustomVars_IfNumberIsLessThanActual()
    {
        $this->assertEquals(5, CustomVariables::getMaxCustomVariables());

        $result = $this->executeCommand(array('maxCustomVars' => 4), true);

        $this->assertContains('Configuring Piwik for 4 custom variables', $result);
        $this->assertContains('1 existing custom variables having the index(es) 5 will be REMOVED.', $result);
        $this->assertContains('Starting to apply changes', $result);
        $this->assertContains('Removed a variable in scope "Page" having the index 5', $result);
        $this->assertContains('Removed a variable in scope "Visit" having the index 5', $result);
        $this->assertContains('Removed a variable in scope "Conversion" having the index 5', $result);
        $this->assertContains('Your Piwik is now configured for 4 custom variables.', $result);

        $this->assertEquals(4, CustomVariables::getMaxCustomVariables());
    }

    public function testExecute_ShouldAddMaxCustomVars_IfNumberIsHigherThanActual()
    {
        $this->assertEquals(5, CustomVariables::getMaxCustomVariables());

        $result = $this->executeCommand(array('maxCustomVars' => 6), true);

        $this->assertContains('Configuring Piwik for 6 custom variables', $result);
        $this->assertContains('1 new custom variables having the index(es) 6 will be ADDED', $result);
        $this->assertContains('Starting to apply changes', $result);
        $this->assertContains('Added a variable in scope "Page" having the index 6', $result);
        $this->assertContains('Added a variable in scope "Visit" having the index 6', $result);
        $this->assertContains('Added a variable in scope "Conversion" having the index 6', $result);
        $this->assertContains('Your Piwik is now configured for 6 custom variables.', $result);

        $this->assertEquals(6, CustomVariables::getMaxCustomVariables());
    }

    public function testExecute_AddMultiple_RemoveMultiple()
    {
        $this->assertEquals(5, CustomVariables::getMaxCustomVariables());

        $this->executeCommand(array('maxCustomVars' => 8), true);
        $this->assertEquals(8, CustomVariables::getMaxCustomVariables());

        $this->executeCommand(array('maxCustomVars' => 3), true);
        $this->assertEquals(3, CustomVariables::getMaxCustomVariables());
    }

    /**
     * @param array $params
     * @param bool  $confirm
     *
     * @return string
     */
    private function executeCommand(array $params, $confirm)
    {
        $confirm = $confirm ? 'yes' : 'no';

        $application = new Application();
        $application->add(new SetNumberOfCustomVariables());

        $command = $application->find('customvariables:set-max-custom-variables');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('dialog');
        $dialog->setInputStream($this->getInputStream($confirm . '\n'));

        $params['command'] = $command->getName();
        $commandTester->execute($params);
        $result = $commandTester->getDisplay();

        return $result;
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}

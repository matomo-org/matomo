<?php
/**
 * Piwik - free/libre analytics platform
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
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomVariables
 * @group CustomVariablesTest
 * @group Plugins
 * @group Plugins
 */
class SetNumberOfCustomVariablesTest extends IntegrationTestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage  Not enough arguments
     */
    public function testExecute_ShouldThrowException_IfArgumentIsMissing()
    {
        $this->executeCommand(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  The number of available custom variables has to be a number
     */
    public function testExecute_ShouldThrowException_HasToBeANumber()
    {
        $this->executeCommand('a');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  There has to be at least five custom variables
     */
    public function testExecute_ShouldThrowException_Minimum2CustomVarsRequired()
    {
        $this->executeCommand(4);
    }

    public function testExecute_ShouldThrowException_IfUserCancelsConfirmation()
    {
        $result = $this->executeCommand(7, false);
        $this->assertStringEndsWith('Are you sure you want to perform these actions? (y/N)', $result);
    }

    public function testExecute_ShouldDoNothingIfExpectedResult_IsAlreadyTheCase()
    {
        $result = $this->executeCommand(5);

        $this->assertContains('Your Piwik is already configured for 5 custom variables', $result);
    }

    public function testExecute_ShouldAddMaxCustomVars_IfNumberIsHigherThanActual()
    {
        $this->assertEquals(5, CustomVariables::getNumUsableCustomVariables());

        $result = $this->executeCommand(6);

        $this->assertContains('Configuring Piwik for 6 custom variables', $result);
        $this->assertContains('1 new custom variables having the index(es) 6 will be ADDED', $result);
        $this->assertContains('Starting to apply changes', $result);
        $this->assertContains('Added a variable in scope "Page" having the index 6', $result);
        $this->assertContains('Added a variable in scope "Visit" having the index 6', $result);
        $this->assertContains('Added a variable in scope "Conversion" having the index 6', $result);
        $this->assertContains('Your Piwik is now configured for 6 custom variables.', $result);

        $this->assertEquals(6, CustomVariables::getNumUsableCustomVariables());
    }

    public function testExecute_ShouldRemoveMaxCustomVars_IfNumberIsLessThanActual()
    {
        $this->executeCommand(6, true);
        $this->assertEquals(6, CustomVariables::getNumUsableCustomVariables());

        $result = $this->executeCommand(5);

        $this->assertContains('Configuring Piwik for 5 custom variables', $result);
        $this->assertContains('1 existing custom variables having the index(es) 6 will be REMOVED.', $result);
        $this->assertContains('Starting to apply changes', $result);
        $this->assertContains('Removed a variable in scope "Page" having the index 6', $result);
        $this->assertContains('Removed a variable in scope "Visit" having the index 6', $result);
        $this->assertContains('Removed a variable in scope "Conversion" having the index 6', $result);
        $this->assertContains('Your Piwik is now configured for 5 custom variables.', $result);

        $this->assertEquals(5, CustomVariables::getNumUsableCustomVariables());
    }

    public function testExecute_AddMultiple_RemoveMultiple()
    {
        $this->assertEquals(5, CustomVariables::getNumUsableCustomVariables());

        $this->executeCommand(9);
        $this->assertEquals(9, CustomVariables::getNumUsableCustomVariables());

        $this->executeCommand(6);
        $this->assertEquals(6, CustomVariables::getNumUsableCustomVariables());
    }

    /**
     * @param int|null $maxCustomVars
     * @param bool  $confirm
     *
     * @return string
     */
    private function executeCommand($maxCustomVars, $confirm = true)
    {
        $setNumberCmd = new SetNumberOfCustomVariables();

        $application = new Application();
        $application->add($setNumberCmd);

        $commandTester = new CommandTester($setNumberCmd);

        $dialog = $setNumberCmd->getHelper('dialog');
        $dialog->setInputStream($this->getInputStream($confirm ? 'yes' : 'no' . '\n'));

        if (is_null($maxCustomVars)) {
            $params = array();
        } else {
            $params = array('maxCustomVars' => $maxCustomVars);
        }

        $params['command'] = $setNumberCmd->getName();
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

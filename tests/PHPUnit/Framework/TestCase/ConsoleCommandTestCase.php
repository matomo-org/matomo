<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestCase;

use Piwik\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * This class is used to workaround a Symfony issue. For tests that need to test interactivity,
 * we need to create a memory stream, set it as the input stream, and force symfony to think it
 * is truly interactive. The forcing is done by ApplicationTester. Unfortunately, the Application::configureIO
 * method will reverse this change if the `posix_isatty` method exists. It will call it on the stream,
 * and since the stream will never be an actual tty, the interactivity will be overwritten.
 *
 * This class gets whether the input is interactive before configureIO is called, and restores
 * it after the method is called.
 */
class TestConsole extends Console
{
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        $isInteractive = $input->isInteractive();

        parent::configureIO($input, $output);

        $input->setInteractive($isInteractive);
    }
}

/**
 * Base class for test cases that test Piwik console commands. Derives from SystemTestCase
 * so the entire Piwik environment is set up.
 *
 * This will create an ApplicationTester instance (provided by Symfony) which should be used to
 * test commands like this:
 *
 *     public function testThisAndThat()
 *     {
 *         $result = $this->applicationTester->run(array(
 *             'command' => 'my-command',
 *             'arg1' => 'value1',
 *             'arg2' => 'value2',
 *             '--option' => true,
 *             '--another-option' => 'value3'
 *         ));
 *         $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
 *
 *         // other checks
 *     }
 *
 * @since 2.8.0
 */
class ConsoleCommandTestCase extends SystemTestCase
{
    /**
     * @var ApplicationTester
     */
    protected $applicationTester = null;

    /**
     * @var Console
     */
    protected $application;

    public function setUp(): void
    {
        parent::setUp();

        $this->application = new TestConsole(self::$fixture->piwikEnvironment);
        $this->application->setAutoExit(false);

        $this->applicationTester = new ApplicationTester($this->application);
    }

    protected function getCommandDisplayOutputErrorMessage()
    {
        return "Command did not behave as expected. Command output: " . $this->applicationTester->getDisplay();
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}

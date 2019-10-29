<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\Monolog\Handler\FailureLogMessageDetector;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

class TestCommandWithWarning extends ConsoleCommand
{
    public function configure()
    {
        parent::configure();

        $this->setName('test-command-with-warning');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        StaticContainer::get(LoggerInterface::class)->warning('warn');
    }
}

class TestCommandWithError extends ConsoleCommand
{
    public function configure()
    {
        parent::configure();

        $this->setName('test-command-with-error');
        $this->addOption('no-error', null, InputOption::VALUE_NONE);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('no-error')) {
            StaticContainer::get(LoggerInterface::class)->error('error');
        }
    }
}

class ConsoleTest extends ConsoleCommandTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->application->addCommands([
            new TestCommandWithWarning(),
            new TestCommandWithError(),
        ]);

        StaticContainer::get(FailureLogMessageDetector::class)->reset();
    }

    public function test_Console_ReturnsCorrectExitCode_IfCommandEmitsWarning()
    {
        $exitCode = $this->applicationTester->run([
            'command' => 'test-command-with-warning',
        ]);
        $this->assertEquals(1, $exitCode);
    }

    public function test_Console_ReturnsCorrectExitCode_IfCommandEmitsError()
    {
        $exitCode = $this->applicationTester->run([
            'command' => 'test-command-with-error',
        ]);
        $this->assertEquals(1, $exitCode);
    }

    public function test_Console_ReturnsCorrectExitCode_IfCommandDoesNotEmitAnything()
    {
        $exitCode = $this->applicationTester->run([
            'command' => 'test-command-with-error',
            '--no-error' => true,
        ]);
        $this->assertEquals(0, $exitCode);
    }

    public static function provideContainerConfigBeforeClass()
    {
        return [
            'log.handlers' => [\DI\get(FailureLogMessageDetector::class)],
            LoggerInterface::class => \DI\object(Logger::class)
                ->constructor('piwik', \DI\get('log.handlers'), \DI\get('log.processors')),
        ];
    }
}
<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\CliMulti\CliPhp;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\Monolog\Handler\FailureLogMessageDetector;
use Piwik\Tests\Framework\Fixture;
use Piwik\Log\LoggerInterface;
use Piwik\Log\Logger;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

class TestCommandWithWarning extends ConsoleCommand
{
    public function configure()
    {
        parent::configure();

        $this->setName('test-command-with-warning');
    }

    public function doExecute(): int
    {
        StaticContainer::get(LoggerInterface::class)->warning('warn');
        return self::SUCCESS;
    }
}

class TestCommandWithError extends ConsoleCommand
{
    public function configure()
    {
        parent::configure();

        $this->setName('test-command-with-error');
        $this->addNoValueOption('no-error');
    }

    public function doExecute(): int
    {
        if (!$this->getInput()->getOption('no-error')) {
            StaticContainer::get(LoggerInterface::class)->error('error');
        }
        return self::SUCCESS;
    }
}

class TestCommandWithFatalError extends ConsoleCommand
{
    public function configure()
    {
        parent::configure();

        $this->setName('test-command-with-fatal-error');
    }

    public function doExecute(): int
    {
        try {
            \Piwik\ErrorHandler::pushFatalErrorBreadcrumb(static::class);

            $this->executeImpl();
        } finally {
            \Piwik\ErrorHandler::popFatalErrorBreadcrumb();
        }

        return self::SUCCESS;
    }

    public function executeImpl()
    {
        try {
            \Piwik\ErrorHandler::pushFatalErrorBreadcrumb(static::class, []);

            $val = "";
            while (true) {
                $val .= str_repeat("*", 1024 * 1024 * 1024);
            }
        } finally {
            \Piwik\ErrorHandler::popFatalErrorBreadcrumb();
        }
    }
}

class TestCommandWithException extends ConsoleCommand
{
    public function configure()
    {
        parent::configure();

        $this->setName('test-command-with-exception');
    }

    public function doExecute(): int
    {
        throw new \Exception('test error');
    }
}

/**
 * @group ConsoleTest
 */
class ConsoleTest extends ConsoleCommandTestCase
{
    public function setUp(): void
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

    public function test_Console_handlesFatalErrorsCorrectly()
    {
        $cliPhp = new CliPhp();
        $php = $cliPhp->findPhpBinary();
        $command = $php . " -i | grep 'memory_limit => -1'";

        $output = shell_exec($command);

        if ($output == "memory_limit => -1 => -1\n") {
            $this->markTestSkipped("no memory limit in php-cli");
        }

        $command = Fixture::getCliCommandBase();
        $command .= ' test-command-with-fatal-error';
        $command .= ' 2>&1';

        $output = shell_exec($command);
        $output = $this->normalizeOutput($output);

        $expected = <<<END

Fatal error: Allowed memory size of X bytes exhausted (tried to allocate X bytes) in /tests/PHPUnit/System/ConsoleTest.php on line 84
*** IN SAFEMODE ***
Matomo encountered an error: Allowed memory size of X bytes exhausted (tried to allocate X bytes) (which lead to: Error: array (
  'type' => 1,
  'message' => 'Allowed memory size of X bytes exhausted (tried to allocate X bytes)',
  'file' => '/tests/PHPUnit/System/ConsoleTest.php',
  'line' => %d,
  'backtrace' => ' on /tests/PHPUnit/System/ConsoleTest.php(%d)
#0 /tests/PHPUnit/System/ConsoleTest.php(%d): Piwik\\\\Tests\\\\System\\\\TestCommandWithFatalError->executeImpl()
#1 /core/Plugin/ConsoleCommand.php(%d): Piwik\\\\Tests\\\\System\\\\TestCommandWithFatalError->doExecute()
',
))
END;

        if (PHP_MAJOR_VERSION < 8) {
            $expected = "#!/usr/bin/env php\n" . $expected;
        }

        $this->assertStringMatchesFormat($expected, $output);
    }

    public function test_Console_handlesExceptionsCorrectly()
    {
        $command = Fixture::getCliCommandBase();
        $command .= ' test-command-with-exception';
        $command .= ' 2>&1';

        $output = shell_exec($command);
        $output = $this->normalizeOutput($output);

        $expected = <<<END
*** IN SAFEMODE ***

In ConsoleTest.php line %d:
              \n  test error  \n              \n
test-command-with-exception


END;

        if (PHP_MAJOR_VERSION < 8) {
            $expected = "#!/usr/bin/env php\n" . $expected;
        }

        $this->assertStringMatchesFormat($expected, $output);
    }

    public static function provideContainerConfigBeforeClass()
    {
        return [
            'log.handlers' => [\Piwik\DI::get(FailureLogMessageDetector::class)],
            LoggerInterface::class => \Piwik\DI::create(Logger::class)
                ->constructor('piwik', \Piwik\DI::get('log.handlers'), \Piwik\DI::get('log.processors')),

            'observers.global' => \Piwik\DI::add([
                ['Console.filterCommands', \Piwik\DI::value(function (&$commands) {
                    $commands[] = TestCommandWithFatalError::class;
                    $commands[] = TestCommandWithException::class;
                })],

                ['Request.dispatch', \Piwik\DI::value(function ($module, $action) {
                    if ($module === 'CorePluginsAdmin' && $action === 'safemode') {
                        print "*** IN SAFEMODE ***\n"; // will appear in output
                    }
                })],
            ]),
        ];
    }

    private function normalizeOutput($output)
    {
        $output = str_replace(PIWIK_INCLUDE_PATH, '', $output);
        $output = preg_replace('/[0-9]+ bytes/', 'X bytes', $output);
        return $output;
    }
}

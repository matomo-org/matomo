<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\tests\Unit\Processor;

use PHPUnit\Runner\Version;
use Piwik\Access;
use Piwik\Common;
use Piwik\Log;
use Piwik\Plugins\Monolog\Processor\ExceptionToTextProcessor;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Processor\ExceptionToTextProcessor
 */
class ExceptionToTextProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var bool
     */
    private $hasSuperUserAccess;

    protected function setUp(): void
    {
        parent::setUp();

        Common::$isCliMode = true;
        $this->hasSuperUserAccess = Access::getInstance()->hasSuperUserAccess();
        Access::getInstance()->setSuperUserAccess(true);
    }

    protected function tearDown(): void
    {
        Access::getInstance()->setSuperUserAccess($this->hasSuperUserAccess);
        parent::tearDown();
    }

    public function testItShouldSkipIfNoException()
    {
        $processor = new ExceptionToTextProcessor();

        $record = array('message' => 'Hello world');

        $this->assertEquals($record, $processor($record));
    }

    public function testItShouldReplaceMessageWithFormattedException()
    {
        $processor = new ExceptionToTextProcessor();
        Log::$debugBacktraceForTests = '[message and stack trace]';

        $exception = new \Exception('Hello world');
        $record = array(
            'context' => array(
                'exception' => $exception,
            ),
        );

        $result = $processor($record);

        $expected = array(
            'message' => __FILE__ . "(%d): [message and stack trace] [Query: , CLI mode: 1]",
            'context' => array(
                'exception' => $exception,
            ),
        );

        $this->assertStringMatchesFormat($expected['message'], $result['message']);
        $this->assertEquals($expected['context'], $result['context']);
        $this->assertEquals(['context', 'message'], array_keys($result));
    }

    public function testItShouldAddSeverityForErrors()
    {
        $processor = new ExceptionToTextProcessor();
        Log::$debugBacktraceForTests = '[message and stack trace]';

        $exception = new \ErrorException('Hello world', 0, 1, 'file.php', 123);
        $record = array(
            'context' => array(
                'exception' => $exception,
            ),
        );

        $result = $processor($record);

        $expected = array(
            'message' => "file.php(123): [message and stack trace] [Query: , CLI mode: 1]",
            'context' => array(
                'exception' => $exception,
            ),
        );

        $this->assertEquals($expected, $result);
    }

    public function testGetMessageAndWholeBacktraceDoesNotPrintBacktraceIfInCliModeAndNotCoreArchive()
    {
        $ex = new \Exception('test message');

        Common::$isCliMode = true;
        unset($_GET['trigger']);

        $wholeTrace = ExceptionToTextProcessor::getMessageAndWholeBacktrace($ex);

        $expected = <<<EOI
test message
EOI;

        $this->assertEquals($expected, $wholeTrace);
    }

    public function testGetMessageAndWholeBacktraceDoesNotPrintBacktraceIfNotInCliModeAndInCoreArchive()
    {
        $ex = new \Exception('test message');

        Common::$isCliMode = false;
        $_GET['trigger'] = 'archivephp';

        $wholeTrace = ExceptionToTextProcessor::getMessageAndWholeBacktrace($ex);

        $expected = <<<EOI
test message
EOI;

        $this->assertEquals($expected, $wholeTrace);
    }

    public function testGetMessageAndWholeBacktracePrintsBacktraceIfInCliModeAndInCoreArchiveEvenIfGlobalVarIsNotSet()
    {
        $ex = new \Exception('test message');

        Common::$isCliMode = true;
        $_GET['trigger'] = 'archivephp';

        $wholeTrace = ExceptionToTextProcessor::getMessageAndWholeBacktrace($ex);
        $wholeTrace = preg_replace('/\\(\\d+\\)/', '', $wholeTrace);
        $wholeTrace = str_replace(PIWIK_INCLUDE_PATH, '', $wholeTrace);

        $expected = <<<EOI
test message
#0 /vendor/phpunit/phpunit/src/Framework/TestCase.php: Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest->testGetMessageAndWholeBacktracePrintsBacktraceIfInCliModeAndInCoreArchiveEvenIfGlobalVarIsNotSet()
#1 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestCase->runTest()
#2 /vendor/phpunit/phpunit/src/Framework/TestResult.php: PHPUnit\\Framework\\TestCase->runBare()
#3 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestResult->run(Object(Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest))
#4 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestCase->run(Object(PHPUnit\\Framework\\TestResult))
#5 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#6 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#7 /vendor/phpunit/phpunit/src/TextUI/TestRunner.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#8 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\TestRunner->doRun(Object(PHPUnit\\Framework\\TestSuite), Array, Array, true)
#9 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\Command->run(Array, true)
#10 /vendor/phpunit/phpunit/phpunit: PHPUnit\\TextUI\\Command::main()
#11 {main}
EOI;

        $this->assertEquals($this->handleNewerPHPUnitTrace($expected), $wholeTrace);
    }

    public function testGetMessageAndWholeBacktracePrintsBacktraceIfPIWIKPRINTERRORBACKTRACEIsDefined()
    {
        $ex = new \Exception('test message');

        $GLOBALS['PIWIK_PRINT_ERROR_BACKTRACE'] = 1;

        $wholeTrace = ExceptionToTextProcessor::getMessageAndWholeBacktrace($ex);
        $wholeTrace = preg_replace('/\\(\\d+\\)/', '', $wholeTrace);
        $wholeTrace = str_replace(PIWIK_INCLUDE_PATH, '', $wholeTrace);

        $expected = <<<EOI
test message
#0 /vendor/phpunit/phpunit/src/Framework/TestCase.php: Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest->testGetMessageAndWholeBacktracePrintsBacktraceIfPIWIKPRINTERRORBACKTRACEIsDefined()
#1 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestCase->runTest()
#2 /vendor/phpunit/phpunit/src/Framework/TestResult.php: PHPUnit\\Framework\\TestCase->runBare()
#3 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestResult->run(Object(Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest))
#4 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestCase->run(Object(PHPUnit\\Framework\\TestResult))
#5 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#6 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#7 /vendor/phpunit/phpunit/src/TextUI/TestRunner.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#8 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\TestRunner->doRun(Object(PHPUnit\\Framework\\TestSuite), Array, Array, true)
#9 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\Command->run(Array, true)
#10 /vendor/phpunit/phpunit/phpunit: PHPUnit\\TextUI\\Command::main()
#11 {main}
EOI;

        $this->assertEquals($this->handleNewerPHPUnitTrace($expected), $wholeTrace);
    }

    public function testGetMessageAndWholeBacktracePrintsBacktraceIfPIWIKTRACKERDEBUGGlobalIsSet()
    {
        $ex = new \Exception('test message');

        $GLOBALS['PIWIK_TRACKER_DEBUG'] = 1;

        $wholeTrace = ExceptionToTextProcessor::getMessageAndWholeBacktrace($ex);
        $wholeTrace = preg_replace('/\\(\\d+\\)/', '', $wholeTrace);
        $wholeTrace = str_replace(PIWIK_INCLUDE_PATH, '', $wholeTrace);

        $expected = <<<EOI
test message
#0 /vendor/phpunit/phpunit/src/Framework/TestCase.php: Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest->testGetMessageAndWholeBacktracePrintsBacktraceIfPIWIKTRACKERDEBUGGlobalIsSet()
#1 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestCase->runTest()
#2 /vendor/phpunit/phpunit/src/Framework/TestResult.php: PHPUnit\\Framework\\TestCase->runBare()
#3 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestResult->run(Object(Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest))
#4 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestCase->run(Object(PHPUnit\\Framework\\TestResult))
#5 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#6 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#7 /vendor/phpunit/phpunit/src/TextUI/TestRunner.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#8 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\TestRunner->doRun(Object(PHPUnit\\Framework\\TestSuite), Array, Array, true)
#9 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\Command->run(Array, true)
#10 /vendor/phpunit/phpunit/phpunit: PHPUnit\\TextUI\\Command::main()
#11 {main}
EOI;

        $this->assertEquals($this->handleNewerPHPUnitTrace($expected), $wholeTrace);
    }

    public function testGetMessageAndWholeBacktraceHandlesArrayInputWhenBacktraceIsEnabled()
    {
        $GLOBALS['PIWIK_PRINT_ERROR_BACKTRACE'] = 1;

        $exArray = [
            'message' => 'themessage',
            'backtrace' => 'thestacktrace',
        ];

        $wholeTrace = ExceptionToTextProcessor::getMessageAndWholeBacktrace($exArray);

        $expected = <<<EOI
themessage
thestacktrace
EOI;

        $this->assertEquals($expected, $wholeTrace);
    }

    public function testGetMessageAndWholeBacktraceHandlesArrayInputWhenBacktraceIsDisabled()
    {
        $exArray = [
            'message' => 'themessage',
            'backtrace' => 'thestacktrace',
        ];

        $wholeTrace = ExceptionToTextProcessor::getMessageAndWholeBacktrace($exArray);

        $expected = 'themessage';

        $this->assertEquals($expected, $wholeTrace);
    }

    public function testGetMessageAndWholeBacktraceShouldCombineCausedByExceptionBacktraces()
    {
        $ex1 = new \Exception('caused by 2');
        $ex2 = new \Exception('caused by 1', 0, $ex1);
        $ex3 = new \Exception('test message', 0, $ex2);

        $GLOBALS['PIWIK_TRACKER_DEBUG'] = 1;

        $wholeTrace = ExceptionToTextProcessor::getMessageAndWholeBacktrace($ex3);
        $wholeTrace = preg_replace('/\\(\\d+\\)/', '', $wholeTrace);
        $wholeTrace = str_replace(PIWIK_INCLUDE_PATH, '', $wholeTrace);

        $expected = <<<EOI
test message
#0 /vendor/phpunit/phpunit/src/Framework/TestCase.php: Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest->testGetMessageAndWholeBacktraceShouldCombineCausedByExceptionBacktraces()
#1 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestCase->runTest()
#2 /vendor/phpunit/phpunit/src/Framework/TestResult.php: PHPUnit\\Framework\\TestCase->runBare()
#3 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestResult->run(Object(Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest))
#4 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestCase->run(Object(PHPUnit\\Framework\\TestResult))
#5 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#6 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#7 /vendor/phpunit/phpunit/src/TextUI/TestRunner.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#8 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\TestRunner->doRun(Object(PHPUnit\\Framework\\TestSuite), Array, Array, true)
#9 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\Command->run(Array, true)
#10 /vendor/phpunit/phpunit/phpunit: PHPUnit\\TextUI\\Command::main()
#11 {main},
caused by: caused by 1
#0 /vendor/phpunit/phpunit/src/Framework/TestCase.php: Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest->testGetMessageAndWholeBacktraceShouldCombineCausedByExceptionBacktraces()
#1 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestCase->runTest()
#2 /vendor/phpunit/phpunit/src/Framework/TestResult.php: PHPUnit\\Framework\\TestCase->runBare()
#3 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestResult->run(Object(Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest))
#4 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestCase->run(Object(PHPUnit\\Framework\\TestResult))
#5 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#6 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#7 /vendor/phpunit/phpunit/src/TextUI/TestRunner.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#8 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\TestRunner->doRun(Object(PHPUnit\\Framework\\TestSuite), Array, Array, true)
#9 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\Command->run(Array, true)
#10 /vendor/phpunit/phpunit/phpunit: PHPUnit\\TextUI\\Command::main()
#11 {main},
caused by: caused by 2
#0 /vendor/phpunit/phpunit/src/Framework/TestCase.php: Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest->testGetMessageAndWholeBacktraceShouldCombineCausedByExceptionBacktraces()
#1 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestCase->runTest()
#2 /vendor/phpunit/phpunit/src/Framework/TestResult.php: PHPUnit\\Framework\\TestCase->runBare()
#3 /vendor/phpunit/phpunit/src/Framework/TestCase.php: PHPUnit\\Framework\\TestResult->run(Object(Piwik\\Plugins\\Monolog\\tests\\Unit\\Processor\\ExceptionToTextProcessorTest))
#4 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestCase->run(Object(PHPUnit\\Framework\\TestResult))
#5 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#6 /vendor/phpunit/phpunit/src/Framework/TestSuite.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#7 /vendor/phpunit/phpunit/src/TextUI/TestRunner.php: PHPUnit\\Framework\\TestSuite->run(Object(PHPUnit\\Framework\\TestResult))
#8 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\TestRunner->doRun(Object(PHPUnit\\Framework\\TestSuite), Array, Array, true)
#9 /vendor/phpunit/phpunit/src/TextUI/Command.php: PHPUnit\\TextUI\\Command->run(Array, true)
#10 /vendor/phpunit/phpunit/phpunit: PHPUnit\\TextUI\\Command::main()
#11 {main}
EOI;

        $this->assertEquals($this->handleNewerPHPUnitTrace($expected), $wholeTrace);
    }

    private function handleNewerPHPUnitTrace($input)
    {
        if (version_compare(Version::id(), '9.0', '>=')) {
            $input = str_replace('TestRunner->doRun', 'TestRunner->run', $input);
        }

        return $input;
    }
}

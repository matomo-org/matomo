<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\tests\Integration;

use Exception;
use Piwik\Application\Environment;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Log;
use Piwik\Plugins\Monolog\tests\Integration\Fixture\LoggerWrapper;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group Log
 */
class LogTest extends IntegrationTestCase
{
    public const TESTMESSAGE = 'test%smessage';
    public const STRING_MESSAGE_FORMAT = '[%tag%] %message%';
    public const STRING_MESSAGE_FORMAT_SPRINTF = "[%s] [%s] %s";

    public static $expectedExceptionOutput = '[Monolog] [<PID>] LogTest.php(%d): dummy error message
  dummy backtrace [Query: , CLI mode: 1]';

    public static $expectedErrorOutput = '[Monolog] [<PID>] dummyerrorfile.php(%d): dummy error message
  dummy backtrace [Query: , CLI mode: 1]';

    public static $expectedErrorOutputWithQuery = '[Monolog] [<PID>] dummyerrorfile.php(%d): dummy error message
  dummy backtrace [Query: ?a=b&d=f, CLI mode: 1]';

    public function setUp(): void
    {
        parent::setUp();

        Log::unsetInstance();

        @unlink(self::getLogFileLocation());
        Log::$debugBacktraceForTests = "dummy error message\ndummy backtrace";
    }

    public function tearDown(): void
    {
        Log::unsetInstance();

        @unlink(self::getLogFileLocation());
        Log::$debugBacktraceForTests = null;

        parent::tearDown();
    }

    /**
     * Data provider for every test.
     */
    public function getBackendsToTest()
    {
        return array(
            'file'     => array('file'),
            'database' => array('database'),
        );
    }

    /**
     * @dataProvider getBackendsToTest
     */
    public function testLoggingWorksWhenMessageIsString($backend)
    {
        $this->recreateLogSingleton($backend);

        Log::warning(self::TESTMESSAGE);

        $this->checkBackend($backend, self::TESTMESSAGE, $formatMessage = true, $tag = 'Monolog');
    }

    /**
     * @dataProvider getBackendsToTest
     */
    public function testLoggingWorksWhenMessageIsSprintfString($backend)
    {
        $this->recreateLogSingleton($backend);

        Log::warning(self::TESTMESSAGE, " subst ");

        $this->checkBackend($backend, sprintf(self::TESTMESSAGE, " subst "), $formatMessage = true, $tag = 'Monolog');
    }

    /**
     * @dataProvider getBackendsToTest
     */
    public function testLoggingWorksWhenMessageIsError($backend)
    {
        $this->recreateLogSingleton($backend);

        $error = new \ErrorException("dummy error string", 0, 102, "dummyerrorfile.php", 145);
        Log::error($error);

        $this->checkBackend($backend, str_replace('<PID>', getmypid(), self::$expectedErrorOutput), $formatMessage = false, $tag = 'Monolog');
    }

    /**
     * @dataProvider getBackendsToTest
     */
    public function testLoggingContextWorks($backend)
    {
        $this->recreateLogSingleton($backend);

        $_SERVER['QUERY_STRING'] = 'a=b&d=f';

        $error = new \ErrorException("dummy error string", 0, 102, "dummyerrorfile.php", 145);
        Log::error($error);

        $this->checkBackend($backend, str_replace('<PID>', getmypid(), self::$expectedErrorOutputWithQuery), $formatMessage = false, $tag = 'Monolog');
    }

    /**
     * @dataProvider getBackendsToTest
     */
    public function testLoggingWorksWhenMessageIsException($backend)
    {
        $this->recreateLogSingleton($backend);

        $exception = new Exception("dummy error message");
        Log::error($exception);

        $this->checkBackend($backend, str_replace('<PID>', getmypid(), self::$expectedExceptionOutput), $formatMessage = false, $tag = 'Monolog');
    }

    /**
     * @dataProvider getBackendsToTest
     */
    public function testLoggingCorrectlyIdentifiesPlugin($backend)
    {
        $this->recreateLogSingleton($backend);

        LoggerWrapper::doLog(self::TESTMESSAGE);

        $this->checkBackend($backend, self::TESTMESSAGE, $formatMessage = true, 'Monolog');
    }

    /**
     * @dataProvider getBackendsToTest
     */
    public function testLogMessagesIgnoredWhenNotWithinLevel($backend)
    {
        $this->recreateLogSingleton($backend, 'ERROR');

        Log::info(self::TESTMESSAGE);

        $this->checkNoMessagesLogged($backend);
    }

    /**
     * @dataProvider getBackendsToTest
     */
    public function testLogMessagesAreTrimmed($backend)
    {
        $this->recreateLogSingleton($backend);

        LoggerWrapper::doLog(" \n   " . self::TESTMESSAGE . "\n\n\n   \n");

        $this->checkBackend($backend, self::TESTMESSAGE, $formatMessage = true, 'Monolog');
    }

    /**
     * @dataProvider getBackendsToTest
     */
    public function testTokenAuthIsRemoved($backend)
    {
        $this->recreateLogSingleton($backend);

        Log::error('token_auth=9b1cefc915ff6180071fb7dcd13ec5a4');

        $this->checkBackend($backend, 'token_auth=removed', $formatMessage = true, $tag = 'Monolog');
    }

    /**
     * The database logs requests at DEBUG level, so we check that there is no recursive
     * loop (logger insert in databases, which logs the query, ...)
     * @link https://github.com/piwik/piwik/issues/7017
     */
    public function testNoInfiniteLoopWhenLoggingToDatabase()
    {
        $this->recreateLogSingleton('database');

        Log::info(self::TESTMESSAGE);

        $this->checkBackend('database', self::TESTMESSAGE, $formatMessage = true, $tag = 'Monolog');
    }

    /**
     * @dataProvider getBackendsToTest
     */
    public function testLoggingNonString($backend)
    {
        $this->recreateLogSingleton($backend);

        Log::warning(123);

        $this->checkBackend($backend, '123', $formatMessage = true, $tag = 'Monolog');
    }

    private function checkBackend($backend, $expectedMessage, $formatMessage = false, $tag = false)
    {
        if ($formatMessage) {
            $expectedMessage = sprintf(self::STRING_MESSAGE_FORMAT_SPRINTF, $tag, getmypid(), $expectedMessage);
        }

        if ($backend == 'file') {
            $this->assertTrue(file_exists(self::getLogFileLocation()));

            $fileContents = file_get_contents(self::getLogFileLocation());
            $fileContents = $this->removePathsFromBacktrace($fileContents);

            $expectedMessage = str_replace("\n ", "\n[Monolog] [" . getmypid() . "]", $expectedMessage);

            $this->assertStringMatchesFormat($expectedMessage . "\n", $fileContents);
        } elseif ($backend == 'database') {
            $queryLog = Db::isQueryLogEnabled();
            Db::enableQueryLog(false);

            $count = Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('logger_message'));
            $this->assertEquals(1, $count);

            $message = Db::fetchOne("SELECT message FROM " . Common::prefixTable('logger_message') . " LIMIT 1");
            $message = $this->removePathsFromBacktrace($message);
            $this->assertStringMatchesFormat($expectedMessage, $message);

            $tagInDb = Db::fetchOne("SELECT tag FROM " . Common::prefixTable('logger_message') . " LIMIT 1");
            if ($tag === false) {
                $this->assertEmpty($tagInDb);
            } else {
                $this->assertEquals($tag, $tagInDb);
            }

            Db::enableQueryLog($queryLog);
        }
    }

    private function checkNoMessagesLogged($backend)
    {
        if ($backend == 'file') {
            $this->assertFalse(file_exists(self::getLogFileLocation()));
        } elseif ($backend == 'database') {
            $this->assertEquals(0, Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('logger_message')));
        }
    }

    private function removePathsFromBacktrace($content)
    {
        return preg_replace_callback("/(?:\/[^\s(<>]+)*\//", function ($matches) {
            if ($matches[0] == '/') {
                return '/';
            } else {
                return '';
            }
        }, $content);
    }

    public static function getLogFileLocation()
    {
        return StaticContainer::get('path.tmp') . '/logs/piwik.test.log';
    }

    private function recreateLogSingleton($backend, $level = 'INFO')
    {
        $newEnv = new Environment('test', array(
            'ini.log.log_writers' => array($backend),
            'ini.log.log_level' => $level,
            'ini.log.string_message_format' => self::STRING_MESSAGE_FORMAT,
            'ini.log.string_message_format_trace' => self::STRING_MESSAGE_FORMAT,
            'ini.log.logger_file_path' => self::getLogFileLocation(),
            Log\LoggerInterface::class => \Piwik\DI::get(Log\Logger::class),
            'Tests.log.allowAllHandlers' => true,
        ));
        $newEnv->init();

        $newMonologLogger = $newEnv->getContainer()->make(Log\LoggerInterface::class);
        $oldLogger = new Log($newMonologLogger);
        Log::setSingletonInstance($oldLogger);
    }
}

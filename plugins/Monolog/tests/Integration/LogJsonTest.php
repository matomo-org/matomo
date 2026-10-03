<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\tests\Integration;

use Exception;
use Piwik\Application\Environment;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Log;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group Log
 */
class LogJsonTest extends IntegrationTestCase
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

    public function testFileLoggingWorksWhenMessageIsString()
    {
        $this->recreateLogSingleton('file');

        Log::warning(self::TESTMESSAGE);

        $expectedMessage = '{"@timestamp":"%d-%d-%dT%d:%d:%d.%d+%d:%d","@source":"%s","@fields":{"channel":"piwik","level":300,"class":"Monolog","request_id":' . getmypid() . '},"@message":"' . self::TESTMESSAGE . '","@tags":["piwik"],"@type":"Matomo"}';

        $this->checkLogFile($expectedMessage);
    }

    public function testFileLoggingWorksWhenMessageIsSprintfString()
    {
        $this->recreateLogSingleton('file');

        Log::warning(self::TESTMESSAGE, " subst ");

        $expectedMessage = '{"@timestamp":"%d-%d-%dT%d:%d:%d.%d+%d:%d","@source":"%s","@fields":{"channel":"piwik","level":300,"class":"Monolog","request_id":' . getmypid() . ',"ctxt_0":" subst "},"@message":"' . sprintf(self::TESTMESSAGE, ' subst ') . '","@tags":["piwik"],"@type":"Matomo"}';

        $this->checkLogFile($expectedMessage);
    }

    public function testFileLoggingWorksWhenMessageIsError()
    {
        $this->recreateLogSingleton('file');

        $error = new \ErrorException("dummy error string", 0, 102, "dummyerrorfile.php", 145);
        Log::error($error);

        $expectedMessage = '{"@timestamp":"%d-%d-%dT%d:%d:%d.%d+%d:%d","@source":"%s","@fields":{"channel":"piwik","level":400,"class":"Monolog","request_id":' . getmypid() . ',"ctxt_exception":{"class":"ErrorException","message":"dummy error string","code":0,"file":"dummyerrorfile.php:145","trace":["phpunit:%d"]}},"@message":"dummyerrorfile.php(145): dummy error message\ndummy backtrace [Query: , CLI mode: 1]","@tags":["piwik"],"@type":"Matomo"}';

        $this->checkLogFile($expectedMessage);
    }

    public function testFileLoggingContextWorks()
    {
        $this->recreateLogSingleton('file');

        $_SERVER['QUERY_STRING'] = 'a=b&d=f';

        $error = new \ErrorException("dummy error string", 0, 102, "dummyerrorfile.php", 145);
        Log::error($error);

        $expectedMessage = '{"@timestamp":"%d-%d-%dT%d:%d:%d.%d+%d:%d","@source":"%s","@fields":{"channel":"piwik","level":400,"class":"Monolog","request_id":' . getmypid() . ',"ctxt_exception":{"class":"ErrorException","message":"dummy error string","code":0,"file":"dummyerrorfile.php:145","trace":["phpunit:%d"]}},"@message":"dummyerrorfile.php(145): dummy error message\ndummy backtrace [Query: ?a=b&d=f, CLI mode: 1]","@tags":["piwik"],"@type":"Matomo"}';

        $this->checkLogFile($expectedMessage);
    }

    public function testFileLoggingWorksWhenMessageIsException()
    {
        $this->recreateLogSingleton('file');

        $exception = new Exception("dummy error message");
        Log::error($exception);

        $expectedMessage = '{"@timestamp":"%d-%d-%dT%d:%d:%d.%d+%d:%d","@source":"%s","@fields":{"channel":"piwik","level":400,"class":"Monolog","request_id":' . getmypid() . ',"ctxt_exception":{"class":"Exception","message":"dummy error message","code":0,"file":"LogJsonTest.php(%d): dummy error message\ndummy backtrace [Query: , CLI mode: 1]","@tags":["piwik"],"@type":"Matomo"}';

        $this->checkLogFile($expectedMessage);
    }

    public function testFileLogMessagesIgnoredWhenNotWithinLevel()
    {
        $this->recreateLogSingleton('file', 'ERROR');

        Log::info(self::TESTMESSAGE);

        $this->checkNoMessagesLogged('file');
    }

    public function testFileTokenAuthIsRemoved()
    {
        $this->recreateLogSingleton('file');

        Log::error('token_auth=9b1cefc915ff6180071fb7dcd13ec5a4');

        $expectedMessage = '{"@timestamp":"%d-%d-%dT%d:%d:%d.%d+%d:%d","@source":"%s","@fields":{"channel":"piwik","level":400,"class":"Monolog","request_id":' . getmypid() . '},"@message":"token_auth=removed","@tags":["piwik"],"@type":"Matomo"}';

        $this->checkLogFile($expectedMessage);
    }

    public function testFileLoggingNonString()
    {
        $this->recreateLogSingleton('file');

        Log::warning(123);

        $expectedMessage = '{"@timestamp":"%d-%d-%dT%d:%d:%d.%d+%d:%d","@source":"%s","@fields":{"channel":"piwik","level":300,"class":"Monolog","request_id":' . getmypid() . '},"@message":"123","@tags":["piwik"],"@type":"Matomo"}';

        $this->checkLogFile($expectedMessage);
    }

    public function testDatabaseLoggingWorksWhenMessageIsString()
    {
        $this->recreateLogSingleton('database');

        Log::warning(self::TESTMESSAGE);

        $expectedMessage = '{"message":"' . self::TESTMESSAGE . '","context":[],"level":300,"level_name":"WARNING","channel":"piwik","datetime":{"date":"%d-%d-%d %d:%d:%d.%d","timezone_type":%d,"timezone":"UTC"},"extra":{"class":"Monolog","request_id":' . getmypid() . '}}';

        $this->checkDatabase($expectedMessage);
    }

    public function testDatabaseLoggingWorksWhenMessageIsSprintfString()
    {
        $this->recreateLogSingleton('database');

        Log::warning(self::TESTMESSAGE, ' subst ');

        $expectedMessage = '{"message":"' . sprintf(self::TESTMESSAGE, ' subst ') . '","context":[" subst "],"level":300,"level_name":"WARNING","channel":"piwik","datetime":{"date":"%d-%d-%d %d:%d:%d.%d","timezone_type":%d,"timezone":"UTC"},"extra":{"class":"Monolog","request_id":' . getmypid() . '}}';

        $this->checkDatabase($expectedMessage);
    }

    public function testDatabaseLoggingWorksWhenMessageIsError()
    {
        $this->recreateLogSingleton('database');

        $error = new \ErrorException("dummy error string", 0, 102, "dummyerrorfile.php", 145);
        Log::error($error);

        $expectedMessage = '{"message":"dummyerrorfile.php(145): dummy error message\ndummy backtrace [Query: , CLI mode: 1]","context":{"exception":{"class":"ErrorException","message":"dummy error string","code":0,"file":"dummyerrorfile.php:145"}},"level":400,"level_name":"ERROR","channel":"piwik","datetime":{"date":"%d-%d-%d %d:%d:%d.%d","timezone_type":%d,"timezone":"UTC"},"extra":{"class":"Monolog","request_id":' . getmypid() . '}}';

        $this->checkDatabase($expectedMessage);
    }

    public function testDatabaseLoggingContextWorks()
    {
        $this->recreateLogSingleton('database');

        $_SERVER['QUERY_STRING'] = 'a=b&d=f';

        $error = new \ErrorException("dummy error string", 0, 102, "dummyerrorfile.php", 145);
        Log::error($error);

        $expectedMessage = '{"message":"dummyerrorfile.php(145): dummy error message\ndummy backtrace [Query: ?a=b&d=f, CLI mode: 1]","context":{"exception":{"class":"ErrorException","message":"dummy error string","code":0,"file":"dummyerrorfile.php:145"}},"level":400,"level_name":"ERROR","channel":"piwik","datetime":{"date":"%d-%d-%d %d:%d:%d.%d","timezone_type":%d,"timezone":"UTC"},"extra":{"class":"Monolog","request_id":' . getmypid() . '}}';

        $this->checkDatabase($expectedMessage);
    }

    public function testDatabaseLoggingWorksWhenMessageIsException()
    {
        $this->recreateLogSingleton('database');

        $exception = new Exception("dummy error message");
        Log::error($exception);

        $expectedMessage = '{"message":"LogJsonTest.php(203): dummy error message\ndummy backtrace [Query: , CLI mode: 1]","context":{"exception":{"class":"Exception","message":"dummy error message","code":0,"file":"LogJsonTest.php:203"}},"level":400,"level_name":"ERROR","channel":"piwik","datetime":{"date":"%d-%d-%d %d:%d:%d.%d","timezone_type":%d,"timezone":"UTC"},"extra":{"class":"Monolog","request_id":' . getmypid() . '}}';

        $this->checkDatabase($expectedMessage);
    }

    public function testDatabaseLogMessagesIgnoredWhenNotWithinLevel()
    {
        $this->recreateLogSingleton('file', 'ERROR');

        Log::info(self::TESTMESSAGE);

        $this->checkNoMessagesLogged('database');
    }

    public function testDatabaseTokenAuthIsRemoved()
    {
        $this->recreateLogSingleton('database');

        Log::error('token_auth=9b1cefc915ff6180071fb7dcd13ec5a4');

        $expectedMessage = '{"message":"token_auth=removed","context":[],"level":400,"level_name":"ERROR","channel":"piwik","datetime":{"date":"%d-%d-%d %d:%d:%d.%d","timezone_type":%d,"timezone":"UTC"},"extra":{"class":"Monolog","request_id":' . getmypid() . '}}';

        $this->checkDatabase($expectedMessage);
    }

    public function testDatabaseLoggingNonString()
    {
        $this->recreateLogSingleton('database');

        Log::warning(123);

        $expectedMessage = '{"message":"123","context":[],"level":300,"level_name":"WARNING","channel":"piwik","datetime":{"date":"%d-%d-%d %d:%d:%d.%d","timezone_type":%d,"timezone":"UTC"},"extra":{"class":"Monolog","request_id":' . getmypid() . '}}';

        $this->checkDatabase($expectedMessage);
    }

    private function checkLogFile($expectedMessage)
    {
        $this->assertTrue(file_exists(self::getLogFileLocation()));

        $fileContents = file_get_contents(self::getLogFileLocation());
        $fileContents = $this->removePathsFromBacktrace($fileContents);

        $this->assertStringMatchesFormat($expectedMessage, $fileContents);
    }

    private function checkDatabase($expectedMessage)
    {
        $queryLog = Db::isQueryLogEnabled();
        Db::enableQueryLog(false);

        $count = Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('logger_message'));
        $this->assertEquals(1, $count);

        $message = Db::fetchOne("SELECT message FROM " . Common::prefixTable('logger_message') . " LIMIT 1");
        $message = $this->removePathsFromBacktrace($message);
        $this->assertStringMatchesFormat($expectedMessage, $message);

        Db::enableQueryLog($queryLog);
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
            'ini.log.log_format' => 'json',
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

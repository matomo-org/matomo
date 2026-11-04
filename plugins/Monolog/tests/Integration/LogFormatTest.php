<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\tests\Integration;

use Piwik\Application\Environment;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\Container;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Log;
use Piwik\Plugins\Monolog\Formatter\JsonFormatter;
use Piwik\Plugins\Monolog\Formatter\LineMessageFormatter;
use Piwik\Plugins\Monolog\Formatter\LogglyFormatter;
use Piwik\Plugins\Monolog\Formatter\LogstashFormatter;
use Piwik\Plugins\Monolog\Handler\DatabaseHandler;
use Piwik\Plugins\Monolog\Handler\ErrorLogHandler;
use Piwik\Plugins\Monolog\Handler\FileHandler;
use Piwik\Plugins\Monolog\Handler\SyslogHandler;
use Piwik\Plugins\Monolog\Handler\WebNotificationHandler;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group Log
 */
class LogFormatTest extends IntegrationTestCase
{
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

    public function testJsonFileLoggingWritesOneJsonObjectPerLine()
    {
        $this->recreateLogSingleton('file', ['ini.log.log_format' => 'json']);

        Log::warning('first %s', 'message');
        Log::info('second message');

        $records = $this->getLogFileRecords();

        $this->assertCount(2, $records);
        $this->assertSame('first message', $records[0]['message']);
        $this->assertSame(['message'], $records[0]['context']);
        $this->assertSame('WARNING', $records[0]['level_name']);
        $this->assertSame('piwik', $records[0]['channel']);
        $this->assertSame('Monolog', $records[0]['extra']['class']);
        $this->assertNotEmpty($records[0]['extra']['request_id']);
        $this->assertSame('second message', $records[1]['message']);
        $this->assertSame('INFO', $records[1]['level_name']);
    }

    public function testJsonFileLoggingKeepsExceptionDetailsInContext()
    {
        $this->recreateLogSingleton('file', ['ini.log.log_format' => 'json']);

        Log::error(new \Exception('dummy error message'));

        $records = $this->getLogFileRecords();

        $this->assertCount(1, $records);
        $this->assertStringContainsString("dummy error message\ndummy backtrace", $records[0]['message']);
        $this->assertSame('Exception', $records[0]['context']['exception']['class']);
        $this->assertSame('dummy error message', $records[0]['context']['exception']['message']);
        $this->assertStringContainsString('LogFormatTest.php:', $records[0]['context']['exception']['file']);
    }

    public function testJsonFileLoggingRedactsSensitiveValues()
    {
        $this->recreateLogSingleton('file', ['ini.log.log_format' => 'json']);

        Config::getInstance()->mail['password'] = 'smtp-secret-value';

        Log::warning('url is %s', 'index.php?token_auth=9b1cefc915ff6180071fb7dcd13ec5a4');
        Log::error(new \Exception('exception mentions smtp-secret-value', 0, new \Exception('caused by smtp-secret-value')));

        $contents = file_get_contents(self::getLogFileLocation());
        $this->assertStringNotContainsString('smtp-secret-value', $contents);
        $this->assertStringNotContainsString('9b1cefc915ff6180071fb7dcd13ec5a4', $contents);

        $records = $this->getLogFileRecords();
        $this->assertSame('url is index.php?token_auth=removed', $records[0]['message']);
        $this->assertSame(['index.php?token_auth=removed'], $records[0]['context']);
        $this->assertSame('exception mentions smtppass', $records[1]['context']['exception']['message']);
        $this->assertStringStartsWith('/plugins/Monolog/tests/Integration/LogFormatTest.php:', $records[1]['context']['exception']['file']);
        $this->assertSame('caused by smtppass', $records[1]['context']['exception']['previous']['message']);
    }

    public function testJsonFileLoggingRedactsFatalErrorAndPhpErrorContext()
    {
        $logger = $this->recreateLogSingleton('file', ['ini.log.log_format' => 'json']);

        Config::getInstance()->mail['password'] = 'smtp-secret-value';

        $logger->error('Fatal error encountered: {exception}', [
            'exception' => [
                'type' => E_ERROR,
                'message' => 'fatal mentions smtp-secret-value',
                'file' => PIWIK_DOCUMENT_ROOT . '/core/Fatal.php',
                'line' => 12,
                'backtrace' => ' on ' . PIWIK_DOCUMENT_ROOT . "/core/Fatal.php(12)\n#0 smtp-secret-value",
            ],
        ]);
        $logger->warning('Notice: something', [
            'trace' => [[
                'file' => PIWIK_DOCUMENT_ROOT . '/core/Notice.php',
                'line' => 34,
                'function' => '{closure:' . PIWIK_DOCUMENT_ROOT . '/config/global.php:12}',
            ]],
        ]);

        $contents = file_get_contents(self::getLogFileLocation());
        $this->assertStringNotContainsString('smtp-secret-value', $contents);
        $this->assertStringNotContainsString(PIWIK_DOCUMENT_ROOT, $contents);

        $records = $this->getLogFileRecords();
        $this->assertSame('fatal mentions smtppass', $records[0]['context']['exception']['message']);
        $this->assertSame('/core/Fatal.php', $records[0]['context']['exception']['file']);
        $this->assertSame(" on /core/Fatal.php(12)\n#0 smtppass", $records[0]['context']['exception']['backtrace']);
        $this->assertSame('/core/Notice.php', $records[1]['context']['trace'][0]['file']);
        $this->assertSame('{closure:/config/global.php:12}', $records[1]['context']['trace'][0]['function']);
    }

    public function testJsonFileLoggingRedactsObjectsInContext()
    {
        $logger = $this->recreateLogSingleton('file', ['ini.log.log_format' => 'json']);

        $url = 'index.php?token_auth=9b1cefc915ff6180071fb7dcd13ec5a4';
        $serializable = new class ($url) implements \JsonSerializable {
            public function __construct(private string $url)
            {
            }

            public function jsonSerialize(): array
            {
                return ['url' => $this->url];
            }
        };
        $plain = new class ($url) {
            public function __construct(public string $url)
            {
            }
        };

        $logger->warning('objects', ['serializable' => $serializable, 'plain' => $plain]);

        $this->assertStringNotContainsString('9b1cefc915ff6180071fb7dcd13ec5a4', file_get_contents(self::getLogFileLocation()));

        $records = $this->getLogFileRecords();
        $this->assertSame(['url' => 'index.php?token_auth=removed'], $records[0]['context']['serializable']);
        $this->assertSame(['url' => 'index.php?token_auth=removed'], $records[0]['context']['plain']);
    }

    public function testLogstashFileLoggingRedactsExceptionTrace()
    {
        $this->recreateLogSingleton('file', ['ini.log.log_format' => 'logstash']);

        Config::getInstance()->mail['password'] = 'smtp-secret-value';

        Log::error(new \Exception('exception mentions smtp-secret-value'));

        $contents = file_get_contents(self::getLogFileLocation());
        $this->assertStringNotContainsString('smtp-secret-value', $contents);
        $this->assertStringNotContainsString(PIWIK_DOCUMENT_ROOT, $contents);

        $records = $this->getLogFileRecords();
        $this->assertSame('exception mentions smtppass', $records[0]['context']['exception']['message']);
        $this->assertNotEmpty($records[0]['context']['exception']['trace']);
        foreach ($records[0]['context']['exception']['trace'] as $frame) {
            $this->assertStringStartsNotWith(PIWIK_DOCUMENT_ROOT, $frame);
        }
    }

    public function testArchiveProfilingLogAlwaysUsesTextFormat()
    {
        $container = $this->createContainer([
            'ini.log.log_format' => 'json',
            'ini.Debug.archive_profiling_log' => self::getLogFileLocation(),
        ]);

        $handlers = $container->get('archiving.performance.handlers');

        $this->assertInstanceOf(LineMessageFormatter::class, $handlers[0]->getFormatter());
    }

    public function testLogglyFileLoggingWritesOneJsonObjectPerLine()
    {
        $this->recreateLogSingleton('file', ['ini.log.log_format' => 'loggly']);

        Log::warning('first message');
        Log::warning('second message');

        $records = $this->getLogFileRecords();

        $this->assertCount(2, $records);
        $this->assertSame('first message', $records[0]['message']);
        $this->assertSame('WARNING', $records[0]['level_name']);
        $this->assertArrayHasKey('timestamp', $records[0]);
        $this->assertArrayNotHasKey('datetime', $records[0]);
        $this->assertSame('second message', $records[1]['message']);
    }

    public function testLogstashFileLoggingWritesOneJsonObjectPerLine()
    {
        $this->recreateLogSingleton('file', ['ini.log.log_format' => 'logstash']);

        Log::warning('first message');
        Log::warning('second message');

        $records = $this->getLogFileRecords();

        $this->assertCount(2, $records);
        $this->assertSame('first message', $records[0]['message']);
        $this->assertSame('WARNING', $records[0]['level']);
        $this->assertSame('Matomo', $records[0]['type']);
        $this->assertSame('Monolog', $records[0]['extra']['class']);
        $this->assertArrayHasKey('@timestamp', $records[0]);
        $this->assertSame('second message', $records[1]['message']);
    }

    public function testJsonDatabaseLoggingStoresJsonMessage()
    {
        $this->recreateLogSingleton('database', ['ini.log.log_format' => 'json']);

        Log::warning('test message');

        $rows = Db::fetchAll("SELECT tag, level, message FROM " . Common::prefixTable('logger_message'));

        $this->assertCount(1, $rows);
        $this->assertSame('Monolog', $rows[0]['tag']);
        $this->assertSame('WARNING', $rows[0]['level']);

        $record = json_decode($rows[0]['message'], true);
        $this->assertSame('test message', $record['message']);
        $this->assertSame('WARNING', $record['level_name']);
    }

    public function testTextFormatIsUsedByDefault()
    {
        $container = $this->createContainer([]);

        $this->assertInstanceOf(LineMessageFormatter::class, $container->make(FileHandler::class)->getFormatter());
        $this->assertInstanceOf(LineMessageFormatter::class, $container->make(DatabaseHandler::class)->getFormatter());
        $this->assertInstanceOf(LineMessageFormatter::class, $container->make(ErrorLogHandler::class)->getFormatter());
        $this->assertInstanceOf(LineMessageFormatter::class, $container->make(SyslogHandler::class)->getFormatter());
    }

    public function testWriterSpecificFormatOverridesGlobalFormat()
    {
        $container = $this->createContainer([
            'ini.log.log_format' => 'JSON',
            'ini.log.log_format_file' => 'text',
            'ini.log.log_format_database' => 'logstash',
            'ini.log.log_format_errorlog' => '',
            'ini.log.log_format_screen' => 'loggly',
        ]);

        $this->assertInstanceOf(LineMessageFormatter::class, $container->make(FileHandler::class)->getFormatter());
        $this->assertInstanceOf(LogstashFormatter::class, $container->make(DatabaseHandler::class)->getFormatter());
        $this->assertInstanceOf(JsonFormatter::class, $container->make(ErrorLogHandler::class)->getFormatter());
        $this->assertInstanceOf(JsonFormatter::class, $container->make(SyslogHandler::class)->getFormatter());
        $this->assertInstanceOf(LineMessageFormatter::class, $container->make(WebNotificationHandler::class)->getFormatter());
    }

    public function testLogglyFormatCanBeConfigured()
    {
        $container = $this->createContainer(['ini.log.log_format' => 'loggly']);

        $this->assertInstanceOf(LogglyFormatter::class, $container->make(FileHandler::class)->getFormatter());
    }

    public function testInvalidFormatThrows()
    {
        $container = $this->createContainer([
            'ini.log.log_format' => 'json',
            'ini.log.log_format_file' => 'xml',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid log format "xml" configured for the "file" log writer. Supported formats are: text, json, loggly, logstash');

        $container->make(FileHandler::class);
    }

    public function testNonStringFormatThrows()
    {
        $container = $this->createContainer(['ini.log.log_format' => ['json']]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid log format "array" configured for the "file" log writer.');

        $container->make(FileHandler::class);
    }

    public function testFormatClassThatIsNoFormatterThrows()
    {
        $container = $this->createContainer([
            'ini.log.log_format' => 'custom',
            'log.formatter.classes' => \Piwik\DI::add(['custom' => \stdClass::class]),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The log format "custom" does not resolve to a Monolog\Formatter\FormatterInterface.');

        $container->make(FileHandler::class);
    }

    private function getLogFileRecords(): array
    {
        $this->assertFileExists(self::getLogFileLocation());

        $lines = explode("\n", rtrim(file_get_contents(self::getLogFileLocation()), "\n"));

        return array_map(function ($line) {
            $record = json_decode($line, true);
            $this->assertIsArray($record, 'Log line is not valid JSON: ' . $line);
            return $record;
        }, $lines);
    }

    public static function getLogFileLocation(): string
    {
        return StaticContainer::get('path.tmp') . '/logs/piwik.test.log';
    }

    private function createContainer(array $definitions): Container
    {
        // pin every format key so a local config.ini.php cannot leak into the test
        $newEnv = new Environment('test', $definitions + [
            'ini.log.log_writers' => ['file'],
            'ini.log.log_level' => 'INFO',
            'ini.log.log_format' => 'text',
            'ini.log.log_format_file' => '',
            'ini.log.log_format_database' => '',
            'ini.log.log_format_errorlog' => '',
            'ini.log.log_format_syslog' => '',
            'ini.log.logger_file_path' => self::getLogFileLocation(),
            Log\LoggerInterface::class => \Piwik\DI::get(Log\Logger::class),
            'Tests.log.allowAllHandlers' => true,
        ]);
        $newEnv->init();

        return $newEnv->getContainer();
    }

    private function recreateLogSingleton(string $backend, array $definitions): Log\LoggerInterface
    {
        $container = $this->createContainer($definitions + ['ini.log.log_writers' => [$backend]]);

        $logger = $container->make(Log\LoggerInterface::class);
        Log::setSingletonInstance(new Log($logger));

        return $logger;
    }
}

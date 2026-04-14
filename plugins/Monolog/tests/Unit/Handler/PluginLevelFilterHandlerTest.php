<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\tests\Unit\Handler;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Piwik\Plugins\Monolog\Handler\PluginLevelFilterHandler;

/**
 * @group Log
 * @covers \Piwik\Plugins\Monolog\Handler\PluginLevelFilterHandler
 */
class PluginLevelFilterHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testIsHandlingUsesLowestConfiguredThreshold()
    {
        $handler = new PluginLevelFilterHandler(new TestHandler(), Logger::WARNING, ['ExamplePlugin' => Logger::INFO]);

        $this->assertTrue($handler->isHandling(['level' => Logger::INFO]));
        $this->assertFalse($handler->isHandling(['level' => Logger::DEBUG]));
    }

    public function testHandleUsesPluginSpecificLevelWhenPluginTagExists()
    {
        $testHandler = new TestHandler();
        $handler = new PluginLevelFilterHandler($testHandler, Logger::WARNING, ['ExamplePlugin' => Logger::INFO]);

        $handler->handle([
            'level' => Logger::INFO,
            'extra' => ['class' => 'ExamplePlugin'],
            'context' => [],
            'message' => 'allowed',
            'formatted' => 'allowed',
            'level_name' => 'INFO',
            'datetime' => new \DateTime(),
        ]);

        $this->assertTrue($testHandler->hasInfoRecords());
    }

    public function testHandleFallsBackToDefaultLevelWithoutPluginOverride()
    {
        $testHandler = new TestHandler();
        $handler = new PluginLevelFilterHandler($testHandler, Logger::WARNING, ['ExamplePlugin' => Logger::INFO]);

        $handler->handle([
            'level' => Logger::INFO,
            'extra' => ['class' => 'OtherPlugin'],
            'context' => [],
            'message' => 'ignored',
            'formatted' => 'ignored',
            'level_name' => 'INFO',
            'datetime' => new \DateTime(),
        ]);

        $this->assertFalse($testHandler->hasInfoRecords());
    }

    public function testHandleBatchFiltersRecordsUsingPerPluginLevels()
    {
        $testHandler = new TestHandler();
        $handler = new PluginLevelFilterHandler($testHandler, Logger::WARNING, ['ExamplePlugin' => Logger::INFO]);

        $handler->handleBatch([
            [
                'level' => Logger::INFO,
                'extra' => ['class' => 'ExamplePlugin'],
                'context' => [],
                'message' => 'allowed',
                'formatted' => 'allowed',
                'level_name' => 'INFO',
                'datetime' => new \DateTime(),
            ],
            [
                'level' => Logger::INFO,
                'extra' => ['class' => 'OtherPlugin'],
                'context' => [],
                'message' => 'ignored',
                'formatted' => 'ignored',
                'level_name' => 'INFO',
                'datetime' => new \DateTime(),
            ],
        ]);

        $this->assertCount(1, $testHandler->getRecords());
        $this->assertSame('allowed', $testHandler->getRecords()[0]['message']);
    }
}

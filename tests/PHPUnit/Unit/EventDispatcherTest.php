<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Piwik\EventDispatcher;
use Piwik\Plugin\Manager;
use RuntimeException;

/**
 * @group Core
 * @group EventDispatcher
 */
class EventDispatcherTest extends TestCase
{
    /**
     * @var MockObject&Manager
     */
    private $pluginManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->pluginManager = $this->createMock(Manager::class);
    }

    public function testPostEventContinuesWhenAPluginHandlerThrowsAnException(): void
    {
        $failingPlugin = new class () {
            public function registerEvents()
            {
                return ['Test.event' => 'onTestEvent'];
            }

            public function onTestEvent()
            {
                throw new RuntimeException('Failing plugin callback');
            }
        };

        $healthyPlugin = new class () {
            public $hasRun = false;

            public function registerEvents()
            {
                return ['Test.event' => 'onTestEvent'];
            }

            public function onTestEvent()
            {
                $this->hasRun = true;
            }
        };

        $this->pluginManager
            ->method('getPluginsLoadedAndActivated')
            ->willReturn([
                'FailingPlugin' => $failingPlugin,
                'HealthyPlugin' => $healthyPlugin,
            ]);

        $dispatcher = new EventDispatcher($this->pluginManager);
        try {
            $dispatcher->postEvent('Test.event', []);
            self::fail('Expected an exception to be rethrown after event dispatch.');
        } catch (RuntimeException $expected) {
            self::assertSame('Failing plugin callback', $expected->getMessage());
        }
        self::assertTrue($healthyPlugin->hasRun);
    }

    public function testPostEventContinuesWhenAnExtraObserverThrowsAnException(): void
    {
        $this->pluginManager
            ->method('getPluginsLoadedAndActivated')
            ->willReturn([]);

        $observerRun = false;
        $dispatcher = new EventDispatcher($this->pluginManager);

        $dispatcher->addObserver('Test.event', static function () {
            throw new RuntimeException('Failing extra observer');
        });

        $dispatcher->addObserver('Test.event', static function () use (&$observerRun) {
            $observerRun = true;
        });

        try {
            $dispatcher->postEvent('Test.event', []);
            self::fail('Expected an exception to be rethrown after event dispatch.');
        } catch (RuntimeException $expected) {
            self::assertSame('Failing extra observer', $expected->getMessage());
        }
        self::assertTrue($observerRun);
    }
}

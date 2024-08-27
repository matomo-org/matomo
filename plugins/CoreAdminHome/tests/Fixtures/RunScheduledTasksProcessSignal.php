<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreAdminHome\tests\Fixtures;

use Closure;
use Piwik\Container\Container;
use Piwik\DI;
use Piwik\Plugins\CoreAdminHome\tests\Fixtures\RunScheduledTasksProcessSignal\StepControl;
use Piwik\Plugins\Monolog\Handler\EchoHandler;
use Piwik\Tests\Framework\Fixture;

/**
 * Provides container configuration and helpers to run process signal tests.
 */
class RunScheduledTasksProcessSignal extends Fixture
{
    public const ENV_TRIGGER = 'MATOMO_TEST_RUN_SCHEDULED_TASKS_PROCESS_SIGNAL';

    /**
     * @var int
     */
    public $idSite = 1;

    /**
     * @var StepControl
     */
    public $stepControl;

    /**
     * @var bool
     */
    private $inTestEnv;

    public function __construct()
    {
        $this->inTestEnv = (bool) getenv(self::ENV_TRIGGER);

        $this->stepControl = new StepControl();
    }

    public function setUp(): void
    {
        Fixture::createSuperUser();

        if (!self::siteCreated($this->idSite)) {
            self::createWebsite('2021-01-01');
        }
    }

    public function tearDown(): void
    {
        // empty
    }

    public function provideContainerConfig(): array
    {
        if (!$this->inTestEnv) {
            return [];
        }

        return [
            'ini.tests.enable_logging' => 1,
            'log.handlers' => static function (Container $c) {
                return [$c->get(EchoHandler::class)];
            },
            'observers.global' => DI::add([
                [
                    'ScheduledTasks.execute',
                    DI::value(Closure::fromCallable([$this->stepControl, 'handleScheduledTasksExecute'])),
                ],
            ]),
        ];
    }
}

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
use Piwik\ArchiveProcessor\Rules;
use Piwik\Config;
use Piwik\Container\Container;
use Piwik\Date;
use Piwik\DI;
use Piwik\Plugins\CoreAdminHome\tests\Fixtures\CoreArchiverProcessSignal\StepControl;
use Piwik\Plugins\Monolog\Handler\EchoHandler;
use Piwik\Plugins\SegmentEditor\API as APISegmentEditor;
use Piwik\Request;
use Piwik\Tests\Framework\Fixture;

/**
 * Fixture that adds one site and tracks one pageview for today.
 *
 * Provides container configuration and helpers to run process signal tests.
 */
class CoreArchiverProcessSignal extends Fixture
{
    public const ENV_TRIGGER = 'MATOMO_TEST_CORE_ARCHIVER_PROCESS_SIGNAL';

    public const TEST_SEGMENT_CH = 'browserCode==CH';
    public const TEST_SEGMENT_FF = 'browserCode==FF';

    /**
     * @var int
     */
    public $idSite = 1;

    /**
     * @var StepControl
     */
    public $stepControl;

    /**
     * @var string
     */
    public $today;

    /**
     * @var bool
     */
    private $inTestEnv;

    /**
     * @var bool
     */
    private $inTestRequest;

    public function __construct()
    {
        $this->inTestEnv = (bool) getenv(self::ENV_TRIGGER);
        $this->inTestRequest = Request::fromRequest()->getBoolParameter(self::ENV_TRIGGER, false);
        $this->today = Date::today()->toString();

        $this->stepControl = new StepControl();
    }

    public function setUp(): void
    {
        Rules::setBrowserTriggerArchiving(false);
        Config::getInstance()->General['process_new_segments_from'] = 'segment_creation_time';
        Fixture::createSuperUser();

        $this->setUpWebsites();
        $this->setUpSegments();
        $this->trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    public function provideContainerConfig(): array
    {
        if (!$this->inTestEnv && !$this->inTestRequest) {
            return [];
        }

        if ($this->inTestRequest) {
            return [
                'observers.global' => DI::add([
                    [
                        'API.CoreAdminHome.archiveReports',
                        DI::value(Closure::fromCallable([$this->stepControl, 'handleAPIArchiveReports'])),
                    ],
                ]),
            ];
        }

        return [
            'ini.tests.enable_logging' => 1,
            'log.handlers' => static function (Container $c) {
                return [$c->get(EchoHandler::class)];
            },
            'observers.global' => DI::add([
                [
                    'CronArchive.alterArchivingRequestUrl',
                    DI::value(static function (&$url) {
                        $url .= '&' . self::ENV_TRIGGER . '=1';
                    }),
                ],
                [
                    'CronArchive.init.finish',
                    DI::value(Closure::fromCallable([$this->stepControl, 'handleCronArchiveStart'])),
                ],
                [
                    'ScheduledTasks.execute',
                    DI::value(Closure::fromCallable([$this->stepControl, 'handleScheduledTasksExecute'])),
                ],
                [
                    'ScheduledTasks.shouldExecuteTask',
                    DI::value(Closure::fromCallable([$this->stepControl, 'handleScheduledTasksShouldExecute'])),
                ]
            ]),
        ];
    }

    private function setUpSegments(): void
    {
        APISegmentEditor::getInstance()->add(
            self::TEST_SEGMENT_CH,
            self::TEST_SEGMENT_CH,
            $this->idSite,
            $autoArchive = true,
            $enabledAllUsers = true
        );

        APISegmentEditor::getInstance()->add(
            self::TEST_SEGMENT_FF,
            self::TEST_SEGMENT_FF,
            $this->idSite,
            $autoArchive = true,
            $enabledAllUsers = true
        );
    }

    private function setUpWebsites(): void
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite('2021-01-01');
        }
    }

    private function trackVisits(): void
    {
        $t = self::getTracker($this->idSite, $this->today, $defaultInit = true);
        $t->setUrl('http://example.org/index.htm');

        self::checkResponse($t->doTrackPageView('0'));
    }
}

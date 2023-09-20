<?php declare(strict_types=1);

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\EventDispatcher;
use Piwik\Option;
use Piwik\Plugin\ReleaseChannels;
use Piwik\UpdateCheck;
use Piwik\UpdateCheck\ReleaseChannel;
use Piwik\Version;
use stdClass;

/**
 * @group Core
 * @group UpdateCheck
 */
class UpdateCheckTest extends TestCase
{
    private const RELEASE_VERSION = '1.2.3';

    /**
     * @var MockObject&Option
     */
    private $mockOptions;

    /**
     * @var mixed
     */
    private $originalAutoUpdateConfig;

    /**
     * @var mixed
     */
    private $originalReleaseChannels;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalAutoUpdateConfig = Config::getInstance()->General['enable_auto_update'] ?? null;
        $this->originalReleaseChannels  = StaticContainer::getContainer()->get('\Piwik\Plugin\ReleaseChannels');

        Config::getInstance()->General['enable_auto_update'] = true;

        $this->mockOptions = $this->getMockBuilder(stdClass::class)
            ->addMethods(['getValue', 'setValue'])
            ->getMock();

        Option::setSingletonInstance($this->mockOptions);

        $releaseChannel = $this->createConfiguredMock(
            ReleaseChannel::class,
            [
                'getUrlToCheckForLatestAvailableVersion' => 'https://matomo.org'
            ]
        );

        $releaseChannels = $this->createconfiguredMock(
            ReleaseChannels::class,
            [
                'getActiveReleaseChannel' => $releaseChannel
            ]
        );

        StaticContainer::getContainer()->set('\Piwik\Plugin\ReleaseChannels', $releaseChannels);
    }

    public function tearDown(): void
    {
        Option::setSingletonInstance(null);
        StaticContainer::getContainer()->set('\Piwik\Plugin\ReleaseChannels', $this->originalReleaseChannels);

        Config::getInstance()->General['enable_auto_update'] = $this->originalAutoUpdateConfig;

        parent::tearDown();
    }

    public function testCheckDoesNothingIfAutoUpdateIsNotEnabled(): void
    {
        Config::getInstance()->General['enable_auto_update'] = false;

        $this->mockOptions
            ->expects(self::never())
            ->method('getValue');

        UpdateCheck::check();
    }

    public function testCheckDoesNothingIfInsideCheckIntervalAndNotForced(): void
    {
        $this->mockOptions
            ->expects(self::once())
            ->method('getValue')
            ->with(UpdateCheck::LAST_TIME_CHECKED)
            ->willReturn((string) time());

        $this->mockOptions
            ->expects(self::never())
            ->method('setValue');

        UpdateCheck::check(false, 86400);
    }

    /**
     * @dataProvider dataCheckRunsIfForcedOrWithinInterval
     *
     * @param false|string $lastTimeChecked
     */
    public function testCheckRunsIfForcedOrWithinInterval(
        bool $forced,
        $lastTimeChecked,
        int $interval
    ): void {
        $this->mockOptions
            ->expects(self::once())
            ->method('getValue')
            ->with(UpdateCheck::LAST_TIME_CHECKED)
            ->willReturn($lastTimeChecked);

        $this->mockOptions
            ->expects(self::exactly(3))
            ->method('setValue')
            ->withConsecutive(
                [UpdateCheck::LAST_TIME_CHECKED, self::greaterThan(0)],
                [UpdateCheck::LAST_CHECK_FAILED, false],
                [UpdateCheck::LATEST_VERSION, self::RELEASE_VERSION]
            );

        EventDispatcher::getInstance()->addObserver(
            'Http.sendHttpRequest',
            static function($aUrl, $httpEventParams, &$response) {
                $response = self::RELEASE_VERSION;
            }
        );

        UpdateCheck::check($forced, $interval);
    }

    /**
     * @return iterable<string, array{bool, false|string, int}>
     */
    public function dataCheckRunsIfForcedOrWithinInterval(): iterable
    {
        yield 'forced' => [true, (string) time(), 86400];
        yield 'never checked before' => [false, false, 86400];
        yield 'interval exceeded' => [false, (string) (time() - 86400), 3600];
    }

    public function testStoresEmptyVersionIfUpdateCheckHttpRequestFails(): void
    {
        $lastTimeChecked = 123456;

        $this->mockOptions
            ->expects(self::once())
            ->method('getValue')
            ->with(UpdateCheck::LAST_TIME_CHECKED)
            ->willReturn($lastTimeChecked);

        $this->mockOptions
            ->expects(self::exactly(4))
            ->method('setValue')
            ->withConsecutive(
                [UpdateCheck::LAST_TIME_CHECKED, self::greaterThan(0)],
                [UpdateCheck::LAST_TIME_CHECKED, $lastTimeChecked],
                [UpdateCheck::LAST_CHECK_FAILED, true],
                [UpdateCheck::LATEST_VERSION, '']
            );

        EventDispatcher::getInstance()->addObserver(
            'Http.sendHttpRequest',
            static function() {
                throw new Exception('test');
            }
        );

        UpdateCheck::check(true);
    }

    public function testGetLatestVersion(): void
    {
        $this->mockOptions
            ->expects(self::once())
            ->method('getValue')
            ->with(UpdateCheck::LATEST_VERSION)
            ->willReturn(self::RELEASE_VERSION);

        self::assertSame(self::RELEASE_VERSION, UpdateCheck::getLatestVersion());
    }

    public function testHasLastCheckFailed(): void
    {
        $this->mockOptions
            ->expects(self::once())
            ->method('getValue')
            ->with(UpdateCheck::LAST_CHECK_FAILED)
            ->willReturn('1');

        self::assertTrue(UpdateCheck::hasLastCheckFailed());
    }

    /**
     * @dataProvider dataIsNewestVersionAvailable
     *
     * @param false|string $expectedResult
     */
    public function testIsNewestVersionAvailable($expectedResult, string $compareTo): void
    {
        $this->mockOptions
            ->expects(self::once())
            ->method('getValue')
            ->with(UpdateCheck::LATEST_VERSION)
            ->willReturn($compareTo);

        self::assertSame($expectedResult, UpdateCheck::isNewestVersionAvailable());
    }

    /**
     * @return iterable<string, array{bool, string}>
     */
    public function dataIsNewestVersionAvailable(): iterable
    {
        yield 'empty version' => [false, ''];
        yield 'older version' => [false, '1.0.0'];
        yield 'same version'  => [false, Version::VERSION];
        yield 'newer version' => ['99.99.99', '99.99.99'];
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

/**
 * The archive resolves a relative date on the site's own day, so the sparkline evolution has to
 * compare against the period before that day, not the one before UTC's.
 *
 * @group Goals
 * @group Plugins
 */
class SparklineEvolutionTimezoneTest extends IntegrationTestCase
{
    /** 04:37 on 2020-12-25 for the site, still 2020-12-24 in UTC. */
    private const NOW = '2020-12-24 16:37:00';

    private int $idSite;

    /** @var array<string, mixed> */
    private array $originalGet;

    /** @var string[] */
    private array $requestedDates = [];

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;

        $this->idSite = Fixture::createWebsite('2020-12-01 00:00:00', 0, 'Site ahead of UTC', false, 1, null, null, 'UTC+12');

        Date::$now = (int) strtotime(self::NOW . ' UTC');
        $this->originalGet = $_GET;
    }

    public function tearDown(): void
    {
        $_GET = $this->originalGet;
        Date::$now = null;

        parent::tearDown();
    }

    public function testEvolutionIsComparedAgainstTheSitesPreviousDay(): void
    {
        $_GET = ['idSite' => (string) $this->idSite, 'period' => 'day', 'date' => 'today', 'segment' => ''];

        Piwik::addAction('API.Request.dispatch', function ($finalParameters, $pluginName, $methodName) {
            if ($pluginName === 'Goals' && $methodName === 'get') {
                $this->requestedDates[] = $finalParameters['date'];
            }
        });

        // The previous period is requested by a filter, once the view loads its own data.
        ViewDataTableFactory::build(Sparklines::ID, 'Goals.get', 'Goals.get', true, false)->render();

        // The other requests read the current period itself. The site's today is 2020-12-25, and
        // UTC's previous day would be 2020-12-23.
        self::assertSame(['2020-12-24'], array_values(array_diff($this->requestedDates, ['today'])));
    }
}

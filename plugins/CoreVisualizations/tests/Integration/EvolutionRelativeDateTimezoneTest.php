<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreVisualizations\tests\Integration;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

/**
 * An evolution graph for a relative date has to plot the days the report beside it covers, and
 * the archive resolves those on the site's own day.
 *
 * @group CoreVisualizations
 * @group Plugins
 */
class EvolutionRelativeDateTimezoneTest extends IntegrationTestCase
{
    /** 04:37 on 2020-12-25 for the site, still 2020-12-24 in UTC. */
    private const NOW = '2020-12-24 16:37:00';

    private int $idSite;

    /** @var array<string, mixed> */
    private array $originalGet;

    /** @var array<string, mixed>|null */
    private ?array $dispatchedParams = null;

    private ?ViewDataTable $view = null;

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;
        Fixture::loadAllTranslations();

        $this->idSite = Fixture::createWebsite('2020-12-01 00:00:00', 0, 'Site ahead of UTC', false, 1, null, null, 'UTC+12');

        Date::$now = (int) strtotime(self::NOW . ' UTC');
        $this->originalGet = $_GET;
    }

    public function tearDown(): void
    {
        $_GET = $this->originalGet;
        Date::$now = null;
        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testARelativeRangeIsPlottedOverTheSitesOwnDays(): void
    {
        $params = $this->getParamsTheGraphRequests(['period' => 'range', 'date' => 'last7']);

        self::assertSame('day', $params['period']);
        self::assertSame('2020-12-19,2020-12-25', $params['date']);
    }

    public function testARelativeCompareDateIsPlottedOnTheSitesOwnDay(): void
    {
        $this->getParamsTheGraphRequests([
            'period' => 'day',
            'date' => '2020-12-25',
            'compareSegments' => [''],
            'comparePeriods' => ['day'],
            'compareDates' => ['yesterday'],
        ]);

        // Comparison parameters reach the API through the request config, not the method's arguments.
        self::assertSame(
            ['2020-12-24,2020-12-24'],
            $this->view->requestConfig->request_parameters_to_modify['compareDates']
        );
    }

    /**
     * Renders the graph up to its API request and returns that request's parameters. The request
     * is aborted there, so no data is loaded.
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function getParamsTheGraphRequests(array $query): array
    {
        $_GET = array_merge(['idSite' => (string) $this->idSite, 'segment' => ''], $query);

        $this->view = ViewDataTableFactory::build(Evolution::ID, 'VisitsSummary.get', 'VisitsSummary.get', true, false);

        Piwik::addAction('API.Request.dispatch', function ($finalParameters, $pluginName, $methodName) {
            if ($pluginName === 'VisitsSummary' && $methodName === 'get') {
                $this->dispatchedParams = $finalParameters;
                throw new \RuntimeException('Evolution graph API request captured.');
            }
        });

        try {
            $this->view->render();
        } catch (\Exception $e) {
            // The API layer re-wraps the abort, so only a failure before the request is rethrown.
            if ($this->dispatchedParams === null) {
                throw $e;
            }
        }

        self::assertNotNull($this->dispatchedParams, 'The evolution graph made no API request.');

        return $this->dispatchedParams;
    }
}

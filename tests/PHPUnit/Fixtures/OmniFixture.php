<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Access;
use Piwik\Option;
use ReflectionClass;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\OverrideLogin;

/**
 * This fixture is the combination of every other fixture defined by Piwik. Should be used
 * with year periods.
 */
class OmniFixture extends Fixture
{
    public $month = '2012-01';
    public $idSite = 'all';
    public $dateTime = '2012-02-01';
    public $now = null;
    public $segment = "browserCode==FF";

    // Visitor profile screenshot test needs visitor id
    public $visitorIdDeterministic = null;

    public $fixtures = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $date = $this->month . '-01';

        $classes = get_declared_classes();
        sort($classes);

        foreach ($classes as $className) {
            if (is_subclass_of($className, 'Piwik\\Tests\\Framework\\Fixture')
                && !is_subclass_of($className, __CLASS__)
                && $className != __CLASS__
                && $className != "Piwik\\Tests\\Fixtures\\SqlDump"
                && $className != "Piwik\\Tests\\Fixtures\\UpdaterTestFixture"
                && $className != "Piwik\\Tests\\Fixtures\\UITestFixture"
            ) {
                $klassReflect = new ReflectionClass($className);
                if (!strpos($klassReflect->getFilename(), "tests/PHPUnit/Fixtures")
                    && $className != "CustomAlerts"
                    && $className != "Piwik\\Plugins\\Insights\\tests\\Fixtures\\SomeVisitsDifferentPathsOnTwoDays"
                    && $className != "Piwik\\Plugins\\Contents\\tests\\Fixtures\\TwoVisitsWithContents"
                ) {
                    continue;
                }

                $fixture = new $className();
                if (!property_exists($fixture, 'dateTime')) {
                    continue;
                }

                $fixture->dateTime = $this->adjustDateTime($fixture->dateTime, $date);

                $this->fixtures[$className] = $fixture;

                $date = Date::factory($date)->addDay(1)->toString();
            }
        }

        $this->now = $this->fixtures['Piwik\\Tests\\Fixtures\\ManySitesImportedLogsWithXssAttempts']->now;

        // make sure ManySitesImportedLogsWithXssAttempts is the first fixture
        $fixture = $this->fixtures['Piwik\\Tests\\Fixtures\\ManySitesImportedLogsWithXssAttempts'];
        unset($this->fixtures['Piwik\\Tests\\Fixtures\\ManySitesImportedLogsWithXssAttempts']);
        $this->fixtures = array_merge(array('Piwik\\Tests\\Fixtures\\ManySitesImportedLogsWithXssAttempts' => $fixture), $this->fixtures);
    }

    private function adjustDateTime($dateTime, $adjustToDate)
    {
        $parts = explode(' ', $dateTime);

        $result = $adjustToDate . ' ';
        $result .= isset($parts[1]) ? $parts[1] : '11:22:33';

        return $result;
    }

    public function setUp()
    {
        foreach ($this->fixtures as $fixture) {
            $fixture->setUp();
        }

        Option::set("Tests.forcedNowTimestamp", $this->now->getTimestamp());

        // launch archiving so tests don't run out of time
        $date = Date::factory($this->dateTime)->toString();
        VisitsSummaryAPI::getInstance()->get($this->idSite, 'year', $date);
        VisitsSummaryAPI::getInstance()->get($this->idSite, 'year', $date, urlencode($this->segment));
    }

    public function tearDown()
    {
        foreach ($this->fixtures as $fixture) {
            $fixture->tearDown();
        }
    }
}

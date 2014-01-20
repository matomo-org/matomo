<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Date;
use Piwik\Tracker\Visit;

/**
 * This fixture is the combination of every other fixture defined by Piwik. Should be used
 * with year periods.
 */
class Test_Piwik_Fixture_OmniFixture extends Test_Piwik_BaseFixture
{
    public $month = '2012-01';
    public $idSite = 'all';
    public $dateTime = '2012-02-01';
    public $now = null;

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
        foreach ($classes as $className) {
            if (is_subclass_of($className, 'Test_Piwik_BaseFixture')
                && $className != __CLASS__
            ) {
                $fixture = new $className();
                if (!property_exists($fixture, 'dateTime')) {
                    continue;
                }

                $fixture->dateTime = $this->adjustDateTime($fixture->dateTime, $date);

                $this->fixtures[$className] = $fixture;

                $date = Date::factory($date)->addDay(1)->toString();
            }
        }

        $this->now = $this->fixtures['Test_Piwik_Fixture_ManySitesImportedLogsWithXssAttempts']->now;

        // make sure Test_Piwik_Fixture_ManySitesImportedLogsWithXssAttempts is the first fixture
        $fixture = $this->fixtures['Test_Piwik_Fixture_ManySitesImportedLogsWithXssAttempts'];
        unset($this->fixtures['Test_Piwik_Fixture_ManySitesImportedLogsWithXssAttempts']);
        $this->fixtures = array_merge(array('Test_Piwik_Fixture_ManySitesImportedLogsWithXssAttempts' => $fixture), $this->fixtures);
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
        foreach ($this->fixtures as $name => $fixture) {
            $fixture->setUp();
        }

        $this->visitorIdDeterministic = bin2hex(\Piwik\Db::fetchOne(
            "SELECT idvisitor FROM " . \Piwik\Common::prefixTable('log_visit')
            . " WHERE idsite = 2 AND location_latitude IS NOT NULL LIMIT 1"));

    }

    public function tearDown()
    {
        foreach ($this->fixtures as $fixture) {
            $fixture->tearDown();
        }
    }
}
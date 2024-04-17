<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\API\Request;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\UsersManager\Model;
use ReflectionClass;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Tests\Framework\Fixture;

/**
 * This fixture is the combination of every other fixture defined by Piwik. Should be used
 * with year periods.
 */
class OmniFixture extends Fixture
{
    const DEFAULT_SEGMENT = "browserCode==FF";
    const OMNIFIXTURE_SUPERUSER_TOKEN = '9ad1de7f8b329ab919d854c556f860c1';

    public $month = '2012-01';
    public $idSite = 'all';

    public $dateTime = '2012-02-01';

    /**
     * @var Date
     */
    public $now = null;
    public $segment = self::DEFAULT_SEGMENT;

    // Visitor profile screenshot test needs visitor id
    public $visitorIdDeterministic = null;

    /**
     * @var Fixture[]
     */
    public $fixtures = array();

    private function requireAllFixtures()
    {
        $fixturesToLoad = array(
            '/tests/PHPUnit/Fixtures/*.php',
            '/tests/UI/Fixtures/*.php',
            '/plugins/*/tests/Fixtures/*.php',
            '/plugins/*/Test/Fixtures/*.php',
        );

        foreach ($fixturesToLoad as $fixturePath) {
            foreach (glob(PIWIK_INCLUDE_PATH . $fixturePath) as $file) {
                require_once $file;
            }
        }
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->requireAllFixtures();

        $date = $this->month . '-01';

        $classes = get_declared_classes();
        sort($classes);

        foreach ($classes as $className) {
            if (
                is_subclass_of($className, 'Piwik\\Tests\\Framework\\Fixture')
                && !is_subclass_of($className, __CLASS__)
                && $className != __CLASS__
                && $className != "Piwik\\Tests\\Fixtures\\SqlDump"
                && $className != "Piwik\\Tests\\Fixtures\\UpdaterTestFixture"
                && $className != "Piwik\\Tests\\Fixtures\\UITestFixture"
                && $className != "Piwik\\Tests\\Fixtures\\VisitsInDifferentTimezones"
            ) {
                $klassReflect = new ReflectionClass($className);
                if (
                    !strpos($klassReflect->getFilename(), "tests/PHPUnit/Fixtures")
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


        if (!empty($this->fixtures['Piwik\\Tests\\Fixtures\\ManySitesImportedLogsWithXssAttempts'])) {
            $this->now = $this->fixtures['Piwik\\Tests\\Fixtures\\ManySitesImportedLogsWithXssAttempts']->now;

            // make sure ManySitesImportedLogsWithXssAttempts is the first fixture
            $fixture = $this->fixtures['Piwik\\Tests\\Fixtures\\ManySitesImportedLogsWithXssAttempts'];
            unset($this->fixtures['Piwik\\Tests\\Fixtures\\ManySitesImportedLogsWithXssAttempts']);
            $this->fixtures = array_merge(array('Piwik\\Tests\\Fixtures\\ManySitesImportedLogsWithXssAttempts' => $fixture), $this->fixtures);
        }
    }

    private function adjustDateTime($dateTime, $adjustToDate)
    {
        $parts = explode(' ', $dateTime);

        $result = $adjustToDate . ' ';
        $result .= isset($parts[1]) ? $parts[1] : '11:22:33';

        return $result;
    }

    public static function getTokenAuth()
    {
        $model = new \Piwik\Plugins\UsersManager\Model();
        $user  = $model->getUser(self::ADMIN_USER_LOGIN);

        if (!empty($user)) {
            if ($model->getUserByTokenAuth(self::OMNIFIXTURE_SUPERUSER_TOKEN)) {
                return self::OMNIFIXTURE_SUPERUSER_TOKEN;
            }
        }
        return parent::getTokenAuth();
    }

    public function setUp(): void
    {
        $firstFixture = array_shift($this->fixtures);
        $this->setUpFixture($firstFixture);

        $initialSitesProperties = SitesManagerAPI::getInstance()->getAllSites();

        foreach ($this->fixtures as $fixture) {
            $this->restoreSitesProperties($initialSitesProperties);

            $this->setUpFixture($fixture);
        }

        $model = new Model();

        if (!$model->getUserByTokenAuth(self::OMNIFIXTURE_SUPERUSER_TOKEN)) {
            $model->addTokenAuth(self::ADMIN_USER_LOGIN, self::OMNIFIXTURE_SUPERUSER_TOKEN, 'omnifixture token', Date::now()->getDatetime());
        }
        if (!$model->getUserByTokenAuth(self::ADMIN_USER_TOKEN)) {
            $model->addTokenAuth(self::ADMIN_USER_LOGIN, self::ADMIN_USER_TOKEN, 'omnifixture token default', Date::now()->getDatetime());
        }

        Option::set("Tests.forcedNowTimestamp", $this->now->getTimestamp());
    }

    public function tearDown(): void
    {
        foreach ($this->fixtures as $fixture) {
            echo "Tearing down " . get_class($fixture) . "...\n";

            $fixture->tearDown();
        }
    }

    private function setUpFixture(Fixture $fixture)
    {
        echo "Setting up " . get_class($fixture) . "...\n";
        $fixture->setUp();
    }

    private function restoreSitesProperties($initialSitesProperties)
    {
        foreach ($initialSitesProperties as $idSite => $properties) {
            Request::processRequest('SitesManager.updateSite', array(
                'idSite' => $idSite,
                'siteName' => $properties['name'],
                'ecommerce' => $properties['ecommerce'],
                'siteSearch' => $properties['sitesearch'],
                'searchKeywordParameters' => $properties['sitesearch_keyword_parameters'],
                'searchCategoryParameters' => $properties['sitesearch_category_parameters'],
                'excludedIps' => $properties['excluded_ips'],
                'excludedQueryParameters' => $properties['excluded_parameters'],
                'timezone' => $properties['timezone'],
                'currency' => $properties['currency'],
                'group' => $properties['group'],
                'startDate' => $properties['ts_created'],
                'excludedUserAgents' => $properties['excluded_user_agents'],
                'excludedReferrers' => $properties['excluded_referrers'],
                'keepURLFragments' => $properties['keep_url_fragment'],
                'type' => $properties['type'],
                'excludeUnknownUrls' => $properties['exclude_unknown_urls']
            ));
        }
    }
}

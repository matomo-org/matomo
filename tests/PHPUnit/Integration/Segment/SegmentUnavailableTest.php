<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Segment;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\Live\SystemSettings;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Plugins\SitesManager\API as APISitesManager;

/**
 * @group Segment
 * @group SegmentUnavailableTest
 */
class SegmentUnavailableTest extends IntegrationTestCase
{
    protected $idSite = 1;

    public function setUp(): void
    {
        parent::setUp();

        // Setup test site
        Date::$now = strtotime('2020-03-01 00:00:00');
        FakeAccess::setIdSitesView([1, 2]);
        FakeAccess::setIdSitesAdmin([3, 4]);
        FakeAccess::$superUser = true;
        FakeAccess::$superUserLogin = 'superusertest';
        APISitesManager::getInstance()->addSite('test', 'http://example.org');

        Option::set(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING, 0);
    }

    public function tearDown() : void
    {
        parent::tearDown();
        Option::set(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING, 1);
    }

    public function testvisitorIdSegmentIsDisabledWhenVisitorProfileUnavailable()
    {
        // Create a new segment that uses the visitorId property
        $definition = 'visitorId==4b3d9389bae51466';
        $name = 'visitorId';
        $visitorSegmentId = API::getInstance()->add($name, $definition, $this->idSite, $autoArchive = 1, $enabledAllUsers = 1);

        // Check it is returned by the API get all segment call
        $this->checkSegmentAvailable($definition, $name, true);

        // Disable the visitor progile
        $this->disableVisitorProfile(true);

        // Check that the new segment is no longer returned by the API
        $this->checkSegmentAvailable($definition, $name, false);

        // Clean up
        API::getInstance()->delete($visitorSegmentId);
        $this->disableVisitorProfile(false);
    }

    public function testUserIdSegmentIsDisabledWhenVisitorProfileUnavailable()
    {
        // Create a new segment that uses the userId property
        $definition = 'userId==4b3d9389bae51466';
        $name = 'userId';
        $userSegmentId = API::getInstance()->add($name, $definition, $this->idSite, $autoArchive = 1, $enabledAllUsers = 1);

        // Check it is returned by the API get all segment call
        $this->checkSegmentAvailable($definition, $name, true);

        // Disable the visitor progile
        $this->disableVisitorProfile(true);

        // Check that the new segment is no longer returned by the API
        $this->checkSegmentAvailable($definition, $name, false);

        // Clean up
        API::getInstance()->delete($userSegmentId);
        $this->disableVisitorProfile(false);
    }

    public function testPluginSegmentIsDisabledWhenPluginUnavailable()
    {

        // Create a new segment that uses the userId property
        $definition = 'browserName==Chrome';
        $name = 'browserName';
        $browserNameSegmentId = API::getInstance()->add($name, $definition, $this->idSite, $autoArchive = 1, $enabledAllUsers = 1);

        // Check it is returned by the API get all segment call
        $this->checkSegmentAvailable($definition, $name, true);

        // Disable the devices detection plugin so the browser name metric will be unavailable
        \Piwik\Plugin\Manager::getInstance()->deactivatePlugin('DevicesDetection');
        \Piwik\Plugin\Manager::getInstance()->unloadPlugin('DevicesDetection');
        $this->flushCaches();

        // Check that the new segment is no longer returned by the API
        $this->checkSegmentAvailable($definition, $name, false);

        // Clean up
        \Piwik\Plugin\Manager::getInstance()->loadPlugin('DevicesDetection');
        \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();
        \Piwik\Plugin\Manager::getInstance()->activatePlugin('DevicesDetection');
        API::getInstance()->delete($browserNameSegmentId);
        $this->flushCaches();
    }

    /**
     * Disable the visitorlog feature
     *
     * @param bool $status
     *
     * @return void
     * @throws \Exception
     */
    private function disableVisitorProfile(bool $status): void
    {
        $settings = new SystemSettings();
        $settings->disableVisitorLog->setValue($status);
        $settings->disableVisitorProfile->setValue($status);
        $settings->save();

        $this->flushCaches();
    }

    /**
     * Flush the caches that contain cached segment data
     *
     * @return void
     */
    private function flushCaches(): void
    {
        Cache::getLazyCache()->flushAll();
        Cache::getEagerCache()->flushAll();
        Cache::getTransientCache()->flushAll();
    }

    /**
     * Check if a segment is available
     *
     * @param string $definition
     * @param string $name
     * @param bool   $shouldBeEnabled
     *
     * @return void
     */
    private function checkSegmentAvailable(string $definition, string $name, bool $shouldBeEnabled): void
    {
        $expected = [];
        if ($shouldBeEnabled) {
            $expected = [0 =>
                [
                    'idsegment' => 1,
                    'name' => $name,
                    'definition' => $definition,
                    'hash' => md5($definition),
                    'login' => 'super user was set',
                    'enable_all_users' => 1,
                    'enable_only_idsite' => 1,
                    'auto_archive' => 1,
                    'ts_last_edit' => null,
                    'deleted' => 0,
                ]
            ];
        }

        $segments = API::getInstance()->getAll($this->idSite);
        if (isset($segments[0]['ts_created'])) {
            unset($segments[0]['ts_created']);
        }

        $this->assertEquals($expected, $segments);
    }
}

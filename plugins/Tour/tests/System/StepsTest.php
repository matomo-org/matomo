<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\tests\System;

use Piwik\Plugins\Tour\Dao\DataFinder;
use Piwik\Plugins\Tour\Engagement\Steps;
use Piwik\Plugins\Tour\tests\Fixtures\SimpleFixtureTrackFewVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Tour
 * @group StepsTest
 * @group Plugins
 */
class StepsTest extends SystemTestCase
{
    /**
     * @var SimpleFixtureTrackFewVisits
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @var Steps
     */
    private $part1;

    public function setUp()
    {
        parent::setUp();

        $this->part1 = new Steps(new DataFinder());
    }

    public function test_getSteps()
    {
        $expected = array(
            array (
                'name' => 'Embed tracking code',
                'key' => 'track_data',
                'done' => true,
                'link' =>
                    array (
                        'module' => 'CoreAdminHome',
                        'action' => 'trackingCodeGenerator',
                        'widget' => false,
                    ),
                'skipped' => false,
            ),
            array (
                'name' => 'Define a goal',
                'key' => 'define_goal',
                'done' => false,
                'link' =>
                    array (
                        'module' => 'Goals',
                        'action' => 'manage',
                        'widget' => false,
                    ),
                'skipped' => false,
            ),
            array (
                'name' => 'Upload your logo',
                'key' => 'setup_branding',
                'done' => false,
                'link' =>
                    array (
                        'module' => 'CoreAdminHome',
                        'action' => 'generalSettings',
                        'widget' => false,
                    ),
                'linkHash' => 'useCustomLogo',
                'skipped' => false,
            ),
            array (
                'name' => 'Add another user',
                'key' => 'add_user',
                'done' => false,
                'link' =>
                    array (
                        'module' => 'UsersManager',
                        'action' => 'index',
                        'widget' => false,
                    ),
                'skipped' => false,
            ),
            array (
                'name' => 'Add another website',
                'key' => 'add_website',
                'done' => false,
                'link' =>
                    array (
                        'module' => 'SitesManager',
                        'action' => 'index',
                        'widget' => false,
                    ),
                'skipped' => false,
            ),
        );

        $this->assertEquals($expected, $this->part1->getSteps());
    }

    public function test_skipStep()
    {
        $steps = $this->part1->getSteps();

        $this->assertFalse($steps[1]['skipped']);
        Steps::skipStep($steps[1]['key']);

        $this->part1->clearCache();

        $steps = $this->part1->getSteps();
        $this->assertTrue($steps[1]['skipped']);
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

}

StepsTest::$fixture = new SimpleFixtureTrackFewVisits();
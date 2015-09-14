<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestAspect;

use Piwik\Application\Environment;
use Piwik\Tests\Framework\Mock\File;
use Piwik\Tests\Framework\TestAspect;
use Piwik\Tests\Framework\TestCase\PiwikTestCase;

/**
 * TODO
 */
class TestWithContainer extends TestAspect
{
    /**
     * @var Environment
     */
    protected $environment;

    public static function isMethodAspect()
    {
        return false;
    }

    public function setUp(PiwikTestCase $testCase)
    {
        File::reset();

        $this->createEnvironment($testCase);
    }

    public function tearDown(PiwikTestCase $testCase)
    {
        $this->destroyEnvironment();

        File::reset();
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    public function createEnvironment($testCase, array $extraConfig = array())
    {
        if (method_exists($testCase, 'provideContainerConfig')) {
            $extraConfig = array_merge($extraConfig, $testCase->provideContainerConfig());
        }

        $this->environment = new Environment($environment = null, $extraConfig, $postBootstrappedEvent = false);
        $this->environment->init();
    }

    public function destroyEnvironment()
    {
        $this->environment->destroy();
    }
}
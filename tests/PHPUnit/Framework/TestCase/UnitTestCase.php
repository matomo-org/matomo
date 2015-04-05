<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestCase;

use Piwik\Application\Environment;
use Piwik\Container\StaticContainer;
use Piwik\EventDispatcher;
use Piwik\Tests\Framework\Mock\File;

/**
 * Base class for Unit tests.
 *
 * @since 2.10.0
 */
abstract class UnitTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Environment
     */
    private $environment;

    public function setUp()
    {
        parent::setUp();

        $this->environment = new Environment('test', $this->provideContainerConfig());
        $this->environment->init();

        File::reset();
        EventDispatcher::getInstance()->clearAllObservers();
    }

    public function tearDown()
    {
        File::reset();

        StaticContainer::clearContainer();

        // make sure the global container exists for the next test case that is executed (since logging can be done
        // before a test sets up an environment)
        $nextTestEnviornment = new Environment('test');
        $nextTestEnviornment->init();

        parent::tearDown();
    }

    /**
     * TODO
     *
     * @return array
     */
    protected function provideContainerConfig()
    {
        return array();
    }
}

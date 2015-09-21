<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestCase;

use Piwik\Application\Environment;
use Piwik\Tests\Framework\Mock\File;
use Piwik\Tests\Framework\TestAspect\TestWithContainer;

/**
 * Base class for Unit tests. Use this if you need to use the DI container in tests. It will be created fresh
 * before each test.
 *
 * @deprecated Unit tests don't need no environment.
 *
 * @since 2.10.0
 *
 * @testWithContainer
 */
abstract class UnitTestCase extends PiwikTestCase
{
    /**
     * @var Environment
     */
    protected $environment;

    public function setUp()
    {
        parent::setUp();

        /** @var TestWithContainer $aspect */
        $aspect = $this->getTestAspect('testWithContainer');

        $this->environment = $aspect->getEnvironment();
    }

    public function tearDown()
    {
        $this->environment = null;

        parent::tearDown();
    }

    /**
     * Use this method to return custom container configuration that you want to apply for the tests.
     *
     * @return array
     */
    public function provideContainerConfig()
    {
        return array();
    }
}

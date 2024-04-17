<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestCase;

use Piwik\Application\Environment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\File;

/**
 * Base class for Unit tests. Use this if you need to use the DI container in tests. It will be created fresh
 * before each test.
 *
 * @deprecated Unit tests don't need no environment.
 *
 * @since 2.10.0
 */
abstract class UnitTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Environment
     */
    protected $environment;

    public function setGroups(array $groups): void
    {
        $pluginName = explode('\\', get_class($this));
        if (!empty($pluginName[2]) && !empty($pluginName[1]) && $pluginName[1] === 'Plugins') {
            // we assume \Piwik\Plugins\PluginName nanmespace...
            if (!in_array($pluginName[2], $groups, true)) {
                $groups[] = $pluginName[2];
            }
        }

        parent::setGroups($groups);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->initEnvironment();

        Fixture::clearInMemoryCaches($resetTranslations = false);
        File::reset();
    }

    public function tearDown(): void
    {
        File::reset();
        Fixture::clearInMemoryCaches($resetTranslations = false);

        // make sure the global container exists for the next test case that is executed (since logging can be done
        // before a test sets up an environment)
        $nextTestEnviornment = new Environment($environment = null, array());
        $nextTestEnviornment->init();

        parent::tearDown();
    }

    /**
     * Use this method to return custom container configuration that you want to apply for the tests.
     *
     * @return array
     */
    protected function provideContainerConfig()
    {
        return array();
    }

    protected function initEnvironment()
    {
        $this->environment = new Environment($environment = null, $this->provideContainerConfig(), $postBootstrappedEvent = false);
        $this->environment->init();
    }
}

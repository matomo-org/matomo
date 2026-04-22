<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Widgetize\tests\Integration;

use Piwik\Plugins\Widgetize\Controller;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Widget\WidgetConfig;

/**
 * @group Widgetize
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    /**
     * @var Controller
     */
    private $controller;
    /**
     * @var array
     */
    private $backupGet;
    /**
     * @var array
     */
    private $backupRequest;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupGet = $_GET;
        $this->backupRequest = $_REQUEST;

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        FakeAccess::clearAccess(
            $superUser = true,
            $idSitesAdmin = [1],
            $idSitesView = [1],
            $identity = 'superUserLogin'
        );

        $_GET = [
            'moduleToWidgetize' => 'Transitions',
            'actionToWidgetize' => 'getTransitions',
            'idSite' => 1,
            'period' => 'day',
            'date' => 'today',
            'widget' => 1,
        ];
        $_REQUEST = $_GET;

        $this->controller = new Controller();
    }

    public function tearDown(): void
    {
        $_GET = $this->backupGet;
        $_REQUEST = $this->backupRequest;

        parent::tearDown();
    }

    public function testIframeShouldBootstrapClientRenderedWidgetForLegacyWidgetizeUrls(): void
    {
        $html = $this->controller->iframe();

        $this->assertStringContainsString('vue-entry="CoreHome.Widget"', $html);
        $this->assertStringContainsString('widgetized="true"', $html);
        $this->assertStringContainsString('clientComponent', $html);
        $this->assertStringContainsString('TransitionsPage', $html);
    }

    public function testBuildClientWidgetMetadataShouldRejectDisabledWidgets(): void
    {
        $config = new WidgetConfig();
        $config->setClientSideComponent('Transitions', 'TransitionsPage');
        $config->disable();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ExceptionWidgetNotEnabled');

        $method = new \ReflectionMethod(Controller::class, 'buildClientWidgetMetadata');
        $method->setAccessible(true);
        $method->invoke($this->controller, $config);
    }

    public function testBuildClientWidgetMetadataShouldIgnoreNonWidgetizableWidgets(): void
    {
        $config = new WidgetConfig();
        $config->setClientSideComponent('Transitions', 'TransitionsPage');
        $config->setIsNotWidgetizable();

        $method = new \ReflectionMethod(Controller::class, 'buildClientWidgetMetadata');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($this->controller, $config));
    }
}

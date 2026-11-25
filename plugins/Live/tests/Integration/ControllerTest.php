<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Integration;

use Piwik\Access;
use Piwik\Exception\ThingNotFoundException;
use Piwik\FrontController;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Live
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    private array $get = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->get = $_GET;

        Fixture::createWebsite('2010-01-01');
    }

    public function tearDown(): void
    {
        $_GET = $this->get;

        parent::tearDown();
    }

    public function testGetVisitorProfilePopupAnswersNotFoundForAnUnknownVisitor()
    {
        $_GET = [
            'module' => 'Live',
            'action' => 'getVisitorProfilePopup',
            'idSite' => '1',
            'period' => 'day',
            'date' => 'today',
            'visitorId' => 'a9f860aa3565d59b',
        ];

        $this->expectException(ThingNotFoundException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Visitor could not be found');

        $outputBufferLevel = ob_get_level();
        try {
            Access::doAsSuperUser(function () {
                FrontController::getInstance()->fetchDispatch('Live', 'getVisitorProfilePopup');
            });
        } finally {
            while (ob_get_level() > $outputBufferLevel) {
                ob_end_clean();
            }
        }
    }
}

<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;
use Piwik\Plugins\ExampleLogTables\VisitorDetails;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Pins both halves of the visits log integration.
 *
 * `extendVisitorDetails()` fills the Live API payload and `renderVisitorDetails()` renders the visits
 * log entry. A plugin that implements only the first one passes every test its author writes and shows
 * nothing in the user interface, which is why this test asserts the rendered HTML too.
 *
 * @group ExampleLogTables
 * @group Plugins
 */
class VisitorDetailsTest extends IntegrationTestCase
{
    private const USER_ID = 'someUserId';

    private VisitorDetails $visitorDetails;

    public function setUp(): void
    {
        parent::setUp();

        Db::query('TRUNCATE TABLE ' . Common::prefixTable(CustomUserLog::TABLE_NAME));

        (new CustomUserLog())->addOrUpdateUserInformation(
            self::USER_ID,
            ['gender' => 'women', 'group_name' => 'admin']
        );

        $this->visitorDetails = new VisitorDetails();
    }

    public function testAddsTheStoredAttributesToTheApiPayload(): void
    {
        $visitor = $this->extendVisitorDetails(self::USER_ID);

        // The gender key is the segment name, which is what makes segment value suggestions work.
        $this->assertSame('women', $visitor['userGender']);
        $this->assertSame('admin', $visitor['userGroup']);
    }

    public function testAddsNothingForAVisitWithoutStoredAttributes(): void
    {
        $visitor = $this->extendVisitorDetails('someoneElse');

        $this->assertSame([], $visitor);
    }

    public function testRendersTheAttributesIntoTheVisitsLogEntry(): void
    {
        $blocks = $this->visitorDetails->renderVisitorDetails($this->extendVisitorDetails(self::USER_ID));

        $this->assertCount(1, $blocks);
        $this->assertSame(40, $blocks[0][0]);
        $this->assertStringContainsString('Gender: women', $blocks[0][1]);
        $this->assertStringContainsString('Group: admin', $blocks[0][1]);
    }

    public function testRendersNothingForAVisitWithoutStoredAttributes(): void
    {
        $this->assertSame([], $this->visitorDetails->renderVisitorDetails([]));
    }

    /**
     * @return array<string, mixed>
     */
    private function extendVisitorDetails(string $userId): array
    {
        $this->visitorDetails->setDetails(['user_id' => $userId]);

        $visitor = [];
        $this->visitorDetails->extendVisitorDetails($visitor);

        return $visitor;
    }
}

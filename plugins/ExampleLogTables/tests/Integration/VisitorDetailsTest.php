<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\tests\Integration;

use Piwik\Common;
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

        // No cleanup needed: IntegrationTestCase restores every table between test methods.
        $userLog = new CustomUserLog();
        $userLog->addOrUpdatePlan(self::USER_ID, 'free');
        $userLog->addOrUpdateAccountName(self::USER_ID, 'acme');

        $this->visitorDetails = new VisitorDetails();
    }

    public function testAddsTheStoredAttributesToTheApiPayload(): void
    {
        $visitor = $this->extendVisitorDetails(self::USER_ID);

        // The plan key is the segment name, which is what makes segment value suggestions work.
        $this->assertSame('free', $visitor['userPlan']);
        $this->assertSame('acme', $visitor['userAccount']);
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
        $this->assertSame(45, $blocks[0][0]);
        $this->assertStringContainsString('Plan:', $blocks[0][1]);
        $this->assertStringContainsString('free', $blocks[0][1]);
        $this->assertStringContainsString('Account:', $blocks[0][1]);
        $this->assertStringContainsString('acme', $blocks[0][1]);

        // Live's icon strip, not a line of text: borrowing the class gives an empty hover tooltip.
        $this->assertStringNotContainsString('visitorLogIconWithDetails', $blocks[0][1]);
    }

    public function testRendersAStoredValueEncodedExactlyOnce(): void
    {
        // What the tracker stores is already HTML-encoded, because the write path sanitises every
        // value before storing it. Printing that under Twig's autoescape would encode it twice and
        // put the entities on screen, which is what `rawSafeDecoded` in the template prevents.
        (new CustomUserLog())->addOrUpdateAccountName(
            self::USER_ID,
            Common::sanitizeInputValue('Sales & Marketing')
        );

        $blocks = $this->visitorDetails->renderVisitorDetails($this->extendVisitorDetails(self::USER_ID));

        $this->assertStringContainsString('Sales &amp; Marketing', $blocks[0][1]);
        $this->assertStringNotContainsString('&amp;amp;', $blocks[0][1]);
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

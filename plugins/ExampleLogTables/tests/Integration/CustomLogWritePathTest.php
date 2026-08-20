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
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Plugins\ExampleLogTables\Dao\CustomAccountLog;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;
use Piwik\Plugins\ExampleLogTables\Tracker\UserAttributesRequestProcessor;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Pins what the tracker write path does with a request that mentions only some of the attributes,
 * and what it does with one that asserts more than it is entitled to.
 *
 * The system test proves the happy path: a site that sends every attribute on every request ends up
 * with the rows and the segments it expects. This test covers the two cases that go wrong quietly --
 * a later request that says nothing about an attribute an earlier one stored, and a request that
 * writes a row other visitors share. Overwriting a stored attribute with a default would erase data
 * no one asked to erase; accepting an unauthenticated paying flag would let any visitor change what
 * every other member of that account sees.
 *
 * It drives the RequestProcessor's two phases directly rather than going through the tracker, because
 * the behaviour under test is a sequence of requests for one user and nothing about a visit matters
 * to it.
 *
 * @group ExampleLogTables
 * @group Plugins
 */
class CustomLogWritePathTest extends IntegrationTestCase
{
    private const USER_ID = 'someUserId';

    private UserAttributesRequestProcessor $processor;

    private CustomUserLog $userLog;

    public function setUp(): void
    {
        parent::setUp();

        // Nothing to clean up: IntegrationTestCase restores every table in the database between
        // test methods, custom log tables included, so each test starts from an empty pair.
        $this->userLog = new CustomUserLog();
        $this->processor = new UserAttributesRequestProcessor($this->userLog, new CustomAccountLog());
    }

    public function testStoresTheAttributesARequestCarries(): void
    {
        $this->record([
            UserAttributesRequestProcessor::PARAM_PLAN => 'free',
            UserAttributesRequestProcessor::PARAM_ACCOUNT => 'acme',
            UserAttributesRequestProcessor::PARAM_ACCOUNT_IS_PAYING => '1',
        ]);

        $this->assertSame(
            ['plan' => 'free', 'account_name' => 'acme'],
            $this->userLog->getUserInformation(self::USER_ID)
        );
        $this->assertSame([['account_name' => 'acme', 'is_paying' => 1]], $this->getAccountRows());
    }

    public function testKeepsTheStoredAccountWhenALaterRequestOnlyCarriesThePlan(): void
    {
        $this->record([
            UserAttributesRequestProcessor::PARAM_PLAN => 'free',
            UserAttributesRequestProcessor::PARAM_ACCOUNT => 'acme',
            UserAttributesRequestProcessor::PARAM_ACCOUNT_IS_PAYING => '1',
        ]);

        $this->record([UserAttributesRequestProcessor::PARAM_PLAN => 'pro']);

        $this->assertSame(
            ['plan' => 'pro', 'account_name' => 'acme'],
            $this->userLog->getUserInformation(self::USER_ID)
        );
    }

    public function testKeepsTheStoredPlanWhenALaterRequestOnlyCarriesTheAccount(): void
    {
        $this->record([UserAttributesRequestProcessor::PARAM_PLAN => 'free']);

        $this->record([UserAttributesRequestProcessor::PARAM_ACCOUNT => 'globex']);

        $this->assertSame(
            ['plan' => 'free', 'account_name' => 'globex'],
            $this->userLog->getUserInformation(self::USER_ID)
        );
    }

    public function testKeepsTheStoredPayingFlagWhenALaterRequestOmitsIt(): void
    {
        $this->record([
            UserAttributesRequestProcessor::PARAM_ACCOUNT => 'acme',
            UserAttributesRequestProcessor::PARAM_ACCOUNT_IS_PAYING => '1',
        ]);

        $this->record([UserAttributesRequestProcessor::PARAM_ACCOUNT => 'acme']);

        $this->assertSame([['account_name' => 'acme', 'is_paying' => 1]], $this->getAccountRows());
    }

    public function testIgnoresAPayingFlagThatIsNeitherZeroNorOne(): void
    {
        $this->record([
            UserAttributesRequestProcessor::PARAM_ACCOUNT => 'acme',
            UserAttributesRequestProcessor::PARAM_ACCOUNT_IS_PAYING => '7',
        ]);

        $this->assertSame([], $this->getAccountRows());

        // An invalid flag is treated as an absent one, so the membership is still recorded: the user
        // row may name an account the account table does not describe yet. That is the designed shape,
        // not a hole -- the account table is reference data about accounts, not a table the user row
        // depends on.
        $this->assertSame(
            ['plan' => '', 'account_name' => 'acme'],
            $this->userLog->getUserInformation(self::USER_ID)
        );
    }

    public function testRejectsAnUnauthenticatedRequestThatAssertsThePayingFlag(): void
    {
        $this->expectException(InvalidRequestParameterException::class);
        $this->expectExceptionMessage(UserAttributesRequestProcessor::PARAM_ACCOUNT_IS_PAYING);

        // The account row is shared between subjects, so a visitor must not be able to assert it. The
        // rejection happens in processRequestParams(), before the visit is persisted, which is what
        // makes the resulting 400 describe a request that was not applied at all.
        $this->validate([
            UserAttributesRequestProcessor::PARAM_ACCOUNT => 'acme',
            UserAttributesRequestProcessor::PARAM_ACCOUNT_IS_PAYING => '1',
        ], $authenticated = false);
    }

    public function testStoresNothingWhenAnUnauthenticatedRequestAssertsThePayingFlag(): void
    {
        try {
            $this->record([
                UserAttributesRequestProcessor::PARAM_PLAN => 'free',
                UserAttributesRequestProcessor::PARAM_ACCOUNT => 'acme',
                UserAttributesRequestProcessor::PARAM_ACCOUNT_IS_PAYING => '1',
            ], self::USER_ID, $authenticated = false);
            $this->fail('the forged flag was accepted');
        } catch (InvalidRequestParameterException $e) {
            // expected
        }

        // Rejecting in the first phase is what makes this assertion possible: the plan the same
        // request carried is not stored either, because nothing reached the write.
        $this->assertSame([], $this->userLog->getUserInformation(self::USER_ID));
        $this->assertSame([], $this->getAccountRows());
    }

    public function testAcceptsAnUnauthenticatedRequestThatAssertsNothingAboutTheAccount(): void
    {
        $this->record([
            UserAttributesRequestProcessor::PARAM_PLAN => 'free',
            UserAttributesRequestProcessor::PARAM_ACCOUNT => 'acme',
        ], self::USER_ID, $authenticated = false);

        // A visitor may describe themselves: this row is keyed on a user id anyone can send, so it
        // is exactly as trustworthy as log_visit.user_id and no gate would improve it.
        $this->assertSame(
            ['plan' => 'free', 'account_name' => 'acme'],
            $this->userLog->getUserInformation(self::USER_ID)
        );
        $this->assertSame([], $this->getAccountRows());
    }

    public function testAcceptsAnUnauthenticatedRequestWhosePayingFlagIsMalformed(): void
    {
        // A malformed value is not a statement, so there is nothing to reject and nothing to store.
        $this->record([
            UserAttributesRequestProcessor::PARAM_ACCOUNT => 'acme',
            UserAttributesRequestProcessor::PARAM_ACCOUNT_IS_PAYING => 'yes',
        ], self::USER_ID, $authenticated = false);

        $this->assertSame([], $this->getAccountRows());
    }

    public function testClampsValuesToTheWidthOfTheColumnThatHoldsThem(): void
    {
        $longAccount = str_repeat('g', CustomAccountLog::MAX_LENGTH_ACCOUNT_NAME + 10);

        $this->record([
            UserAttributesRequestProcessor::PARAM_PLAN => str_repeat('w', CustomUserLog::MAX_LENGTH_PLAN + 10),
            UserAttributesRequestProcessor::PARAM_ACCOUNT => $longAccount,
            UserAttributesRequestProcessor::PARAM_ACCOUNT_IS_PAYING => '1',
        ]);

        $clampedAccount = substr($longAccount, 0, CustomAccountLog::MAX_LENGTH_ACCOUNT_NAME);

        // Both sides of the join are clamped by the same rule, so the two tables still meet. Left to
        // the database, the tracker's strict sql_mode would have rejected the whole tracking request.
        $this->assertSame(
            [
                'plan' => str_repeat('w', CustomUserLog::MAX_LENGTH_PLAN),
                'account_name' => $clampedAccount,
            ],
            $this->userLog->getUserInformation(self::USER_ID)
        );
        $this->assertSame([['account_name' => $clampedAccount, 'is_paying' => 1]], $this->getAccountRows());
    }

    public function testStoresTrackingValuesSanitisedRatherThanRaw(): void
    {
        $this->record([UserAttributesRequestProcessor::PARAM_ACCOUNT => 'Sales & Marketing']);

        // The request API returns raw values, so the write path sanitises before storing. Core's log
        // tables hold sanitised values and the visits log template decodes them exactly once through
        // `rawSafeDecoded`, so a table holding the raw value would print its entities on screen.
        $this->assertSame(
            ['plan' => '', 'account_name' => 'Sales &amp; Marketing'],
            $this->userLog->getUserInformation(self::USER_ID)
        );
    }

    public function testDropsAnEntityTheClampCutInHalf(): void
    {
        // Sanitising turns the trailing `&` into `&amp;`, which pushes the value past the column and
        // leaves `&am` at the cut. Storing that shows the literal text "&am" in the visits log.
        $this->record([
            UserAttributesRequestProcessor::PARAM_ACCOUNT
                => str_repeat('g', CustomAccountLog::MAX_LENGTH_ACCOUNT_NAME - 3) . '&',
        ]);

        $this->assertSame(
            ['plan' => '', 'account_name' => str_repeat('g', CustomAccountLog::MAX_LENGTH_ACCOUNT_NAME - 3)],
            $this->userLog->getUserInformation(self::USER_ID)
        );
    }

    public function testKeepsAnEntityThatFitsWholeInsideTheColumn(): void
    {
        // The guard drops a *partial* entity only. One that survives the cut intact is left alone,
        // so a name with an ampersand in it still round-trips.
        $this->record([UserAttributesRequestProcessor::PARAM_ACCOUNT => 'R&D']);

        $this->assertSame(
            ['plan' => '', 'account_name' => 'R&amp;D'],
            $this->userLog->getUserInformation(self::USER_ID)
        );
    }

    public function testStoresNothingForAVisitWithoutAUserId(): void
    {
        $this->record([UserAttributesRequestProcessor::PARAM_PLAN => 'free'], '');

        $this->assertSame([], $this->userLog->getUserInformation(self::USER_ID));
        $this->assertSame([], $this->getAccountRows());
    }

    public function testStoresNothingWhenTheRequestCarriesNoneOfTheAttributes(): void
    {
        $this->record(['url' => 'https://example.org/']);

        $this->assertSame([], $this->userLog->getUserInformation(self::USER_ID));
    }

    /**
     * Runs both phases in the order Tracker\Visit::handle() runs them.
     *
     * @param array<string, string> $params
     */
    private function record(array $params, string $userId = self::USER_ID, bool $authenticated = true): void
    {
        $request = $this->buildRequest($params, $authenticated);

        $this->processor->processRequestParams(new VisitProperties(), $request);
        $this->processor->recordLogs(new VisitProperties(['user_id' => $userId]), $request);
    }

    /**
     * Runs the validating phase alone.
     *
     * @param array<string, string> $params
     */
    private function validate(array $params, bool $authenticated = true): void
    {
        $this->processor->processRequestParams(
            new VisitProperties(),
            $this->buildRequest($params, $authenticated)
        );
    }

    /**
     * @param array<string, string> $params
     */
    private function buildRequest(array $params, bool $authenticated): Request
    {
        $request = new AuthenticatableRequest($params);

        if ($authenticated) {
            $request->markAuthenticated();
        }

        return $request;
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getAccountRows(): array
    {
        return Db::fetchAll(
            'SELECT * FROM ' . Common::prefixTable(CustomAccountLog::TABLE_NAME) . ' ORDER BY account_name'
        );
    }
}

/**
 * Lets a test say the request carried a valid token without one existing.
 *
 * `Tracker\Request::$isAuthenticated` is protected and there is no public setter, so a subclass is
 * how you reach it. Core's own tracker tests do exactly this -- see `TestRequest` at the foot of
 * `tests/PHPUnit/Unit/Tracker/RequestTest.php` -- and it is a test device, not plugin API.
 */
class AuthenticatableRequest extends Request
{
    public function markAuthenticated(): void
    {
        $this->isAuthenticated = true;
    }
}

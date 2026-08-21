<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\Tracker;

use Piwik\Common;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Plugins\ExampleLogTables\Dao\CustomAccountLog;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Validates and writes the plugin's own log tables during tracking.
 *
 * A Dimension writes a column of a log table Matomo already owns. Writing rows into a table of your
 * own is what a RequestProcessor is for.
 *
 * **This class uses two phases, and which phase does what is the lesson.** `Tracker\Visit::handle()`
 * calls `manipulateRequest()`, then `processRequestParams()`, then `afterRequestProcessed()`, then
 * persists the visit, and only then `recordLogs()`.
 *
 * - **The phase you validate in decides whether a rejection is clean.** Rejecting a request means
 *   throwing, and a throw reaches `Tracker::main()`, which answers HTTP 400. Thrown from
 *   `recordLogs()` that 400 describes a request the tracker has already half applied -- the visit is
 *   stored, and so is part of what this plugin writes. Thrown from `processRequestParams()` nothing
 *   has been stored yet, so the answer is true.
 * - **The phase you write in is decided by what data exists yet.** The write below is keyed on the
 *   `user_id` the visit persisted, which does not exist until the visit is stored, so it cannot
 *   happen before `recordLogs()`.
 *
 * They are rarely the same phase, and splitting them is cheaper than discovering the overlap later.
 *
 * `recordLogs()` runs once per tracking *request*, not once per visit, so a visit of ten pageviews
 * calls it ten times -- which is why the DAO upserts instead of inserting. It is skipped entirely
 * for a request that was aborted earlier (an excluded visit, a late ping) and for requests handled in
 * bot mode, so it is the right place to write data about a visit and the wrong place to count
 * requests. Other plugins' `recordLogs()` run in the same loop, so half of them run after this one --
 * do not depend on their work here.
 *
 * Matomo finds this class because it lives in the plugin's `Tracker/` directory and extends
 * `Piwik\Tracker\RequestProcessor` -- see `Piwik\Plugin\RequestProcessors`. It is instantiated
 * through the container, so its dependencies are injected. It is also shared between tracking
 * requests, so it must stay stateless: the flag is read again in the second phase rather than
 * remembered from the first.
 */
class UserAttributesRequestProcessor extends RequestProcessor
{
    /**
     * Tracking parameters this plugin reads. A plugin makes up its own; nothing registers them.
     */
    public const PARAM_PLAN = 'user_plan';
    public const PARAM_ACCOUNT = 'user_account';
    public const PARAM_ACCOUNT_IS_PAYING = 'user_account_is_paying';

    /**
     * Returned by readPayingFlag() when the request made no usable statement about the flag.
     */
    private const FLAG_ABSENT = -1;

    private CustomUserLog $userLog;

    private CustomAccountLog $accountLog;

    public function __construct(CustomUserLog $userLog, CustomAccountLog $accountLog)
    {
        $this->userLog = $userLog;
        $this->accountLog = $accountLog;
    }

    /**
     * @return bool
     */
    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        if (self::FLAG_ABSENT === $this->readPayingFlag($request)) {
            return false;
        }

        // The account row is shared: it is one row per account, with no idsite, read by every user of
        // that account on every site of the install. One forged request would therefore change what
        // other people see and every site's archived metric, for subjects who never sent a request.
        //
        // Whose data a forged request moves is what earns the token, not the sensitivity of the
        // value. The user's own attributes below take no gate because anyone can send any `uid`
        // anyway: a forged one already writes a `log_visit` row under that id, so refusing to write
        // this one alongside it protects nothing. A row other people share is different in kind.
        //
        // What that argument does *not* cover, and the gate would not fix: these rows carry no
        // idsite, so the trust boundary they sit on is the install, not the site. A request tracked
        // against any site of the install rewrites the plan and account name that every other site
        // displays for that user id -- something `log_visit.user_id`, being per-site, cannot do. The
        // way out of that one is an idsite column, not a token; it is accepted here and written up
        // under *Privacy* in the README.
        //
        // Core gates this class of parameter the same way, and throws rather than ignoring: `cty`
        // and the other location overrides in `plugins/UserCountry/Columns/Base.php`, `cip` and
        // `cdt` in `core/Tracker/Request.php`.
        if (!$request->isAuthenticated()) {
            throw new InvalidRequestParameterException(sprintf(
                "Tracker API '%s' describes an entity shared between visitors, requires valid token_auth",
                self::PARAM_ACCOUNT_IS_PAYING
            ));
        }

        return false;
    }

    public function recordLogs(VisitProperties $visitProperties, Request $request)
    {
        // Read the user id Matomo persisted rather than the `uid` request parameter. By this point
        // the site's "disable user id" setting and PrivacyManager's user id pseudonymisation have
        // both been applied, and this table must not hold an identifier log_visit does not.
        $userId = $visitProperties->getProperty('user_id');

        if (empty($userId) || !is_string($userId)) {
            return;
        }

        // The tracker's own `Request::getParam()` accepts only parameters core knows about and throws
        // for anything else, so a plugin reads its own by wrapping the request's parameter array in
        // the request API. Note the fully qualified name: `Piwik\Request` is that API, and it is
        // unrelated to the `Piwik\Tracker\Request` imported above.
        $params = new \Piwik\Request($request->getParams());

        // These arrive raw. The request API filters null bytes and nothing else, so sanitise
        // deliberately -- core's log tables hold sanitised values, which is why every visits log
        // template prints them through `rawSafeDecoded`, and a table holding raw values would be the
        // odd one out. Sanitise before clamping, never after: encoding expands a value, so thirty
        // clamped ampersands would become a hundred and fifty stored characters and fail the whole
        // tracking request. Core reaches the same order by a different route -- the tracker sanitises
        // every string parameter as it reads it, and `CoreHome\Columns\UserId` then truncates a value
        // that is already sanitised. Sanitising here is the step the request API does not do for you.
        $plan = Common::sanitizeInputValue($params->getStringParameter(self::PARAM_PLAN, ''));
        $account = Common::sanitizeInputValue($params->getStringParameter(self::PARAM_ACCOUNT, ''));

        // Clamp each value to the width of the column that holds it. These arrive from a tracking
        // request, so their length is not yours to assume: the tracker connection keeps whatever
        // sql_mode the server gives it, which on a default install is strict, and an over-long
        // value there fails the whole tracking request rather than truncating. Clamping is lossy
        // in its own way -- two account names sharing a 30-character prefix become one row, and
        // `account_name` is the join key between the two tables -- so widen the column rather than
        // lean on the clamp if your values are genuinely free text. The account name is clamped before
        // either table is written, so both sides of that join always hold the same string.
        $plan = $this->clamp($plan, CustomUserLog::MAX_LENGTH_PLAN);
        $account = $this->clamp($account, CustomAccountLog::MAX_LENGTH_ACCOUNT_NAME);

        if ('' === $plan && '' === $account) {
            return; // nothing this plugin collects was sent, so nothing is stored
        }

        // One call per attribute the request carried. A request that mentions the plan but not the
        // group calls nothing that writes the account column, which is what keeps it from erasing what
        // an earlier request stored -- see the DAO for why that is a method per attribute rather
        // than an array of column names.
        if ('' !== $plan) {
            $this->userLog->addOrUpdatePlan($userId, $plan);
        }

        if ('' !== $account) {
            $this->userLog->addOrUpdateAccountName($userId, $account);
        }

        // The account row is written only when this request said something about the flag. A request
        // carrying a account name and nothing else stores the user's membership and leaves the account
        // table alone, so a user row can name an account that has no row of its own yet -- deliberately.
        // The account table is reference data about accounts, not a foreign key the user table depends
        // on: an account nobody has described yet is simply an account with no known flag, and the
        // archived metric counts it as not-a-paying-account until a request says otherwise.
        $isPaying = $this->readPayingFlag($request);

        if ('' === $account || self::FLAG_ABSENT === $isPaying) {
            return;
        }

        // `is_paying` is a segmentation attribute describing the account, never an authorisation
        // signal -- no access decision anywhere in Matomo may read it. The request that set it was
        // authenticated, which is what makes the value worth storing at all; see the first phase.
        $this->accountLog->addOrUpdateAccountInformation($account, 1 === $isPaying);
    }

    /**
     * Cuts a sanitised value down to the number of characters its column holds.
     */
    private function clamp(string $sanitised, int $maxLength): string
    {
        $clamped = mb_substr($sanitised, 0, $maxLength);

        // Sanitising expands a value, so the cut can land inside an HTML entity: thirty characters
        // of `xxx...x&` is `xxx...x&am`, and what the visits log then shows is the literal text
        // "&am". Dropping a trailing partial entity is the other half of doing this in this order.
        // Clamping first would overflow the column instead, which is worse -- so both directions of
        // the interaction between encoding and clamping need handling, not just one.
        return preg_replace('/&[a-zA-Z0-9#]*$/', '', $clamped);
    }

    /**
     * Returns 0 or 1 when the request asserted the paying flag, and FLAG_ABSENT when it did not.
     */
    private function readPayingFlag(Request $request): int
    {
        // A default of -1 distinguishes "the request said the account is not a paying account" from "the
        // request said nothing about it". Writing an invented default in the second case would
        // silently overwrite what an earlier request stored. A value that is not an integer returns
        // the default as well, so a malformed flag is indistinguishable from an absent one -- and
        // both are treated as no statement, so neither is rejected above and neither is stored below.
        $isPaying = (new \Piwik\Request($request->getParams()))
            ->getIntegerParameter(self::PARAM_ACCOUNT_IS_PAYING, self::FLAG_ABSENT);

        return 0 === $isPaying || 1 === $isPaying ? $isPaying : self::FLAG_ABSENT;
    }
}

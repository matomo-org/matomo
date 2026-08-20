<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\tests\Integration;

use Piwik\Date;
use Piwik\Metrics;
use Piwik\Plugins\API\API as ApiPlugin;
use Piwik\Plugins\ExampleLogTables\ExampleLogTables;
use Piwik\Plugins\ExampleLogTables\RecordBuilders\PayingAccountVisits;
use Piwik\Plugins\ExampleLogTables\tests\Fixtures\VisitsWithUserIdAndCustomData;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\API\Request;

/**
 * Pins the two naming contracts this plugin depends on, both of which fail silently when broken.
 *
 * A segment name is also a key in the Live payload, and a record name is also a translation key. Get
 * either wrong and everything still works: the segment filters correctly and the metric archives
 * correctly, but the segment editor offers no values and the metric shows up under its raw record
 * name. Nothing logs either, which is why they are asserted here rather than trusted to the prose
 * that explains them.
 *
 * @group ExampleLogTables
 * @group Plugins
 */
class SegmentAndMetricNamesTest extends IntegrationTestCase
{
    /**
     * @var VisitsWithUserIdAndCustomData
     */
    public static $fixture; // initialized below class definition

    /**
     * @param \Piwik\Tests\Framework\Fixture $fixture
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);

        $fixture->createSuperUser = true;
    }

    private int $originalLookBack;

    public function setUp(): void
    {
        parent::setUp();

        // The test environment installs a translator that returns every key unchanged, which would
        // make the metric assertions below unable to tell a translation key from a translated string
        // -- the exact confusion they exist to catch. Real translations have to be loaded for them to
        // mean anything.
        Fixture::loadAllTranslations();

        // One test widens the auto-suggest look-back. It is a public static, so it has to be put back
        // or it leaks into every test that runs after this class in the same process -- core's own
        // auto-suggest test saves and restores it for the same reason.
        $this->originalLookBack = ApiPlugin::$_autoSuggestLookBack;
    }

    public function tearDown(): void
    {
        ApiPlugin::$_autoSuggestLookBack = $this->originalLookBack;

        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testSuggestsPlanValuesReadFromTheVisitsLogPayload(): void
    {
        // Suggestions come out of the visits log, and core only looks a fixed number of days back.
        // The fixture's visits are historical, so the window has to be widened to reach them --
        // which is itself worth knowing: a segment over data older than the window suggests nothing.
        $trackedOn = Date::factory(self::$fixture->dateTime);
        ApiPlugin::$_autoSuggestLookBack = 1 + (int) ceil(
            (Date::today()->getTimestamp() - $trackedOn->getTimestamp()) / 86400
        );

        $values = Request::processRequest('API.getSuggestedValuesForSegment', [
            'segmentName' => 'userPlan',
            'idSite' => self::$fixture->idSite,
        ]);

        sort($values);

        // The values are here only because VisitorDetails publishes them under `userPlan`, the
        // same string as the dimension's $segmentName. Rename one and this list is empty.
        $this->assertSame(['free', 'pro'], $values);
    }

    public function testSuggestsThePayingFlagValuesFromItsOwnCallback(): void
    {
        $values = Request::processRequest('API.getSuggestedValuesForSegment', [
            'segmentName' => 'accountIsPaying',
            'idSite' => self::$fixture->idSite,
        ]);

        // Nothing publishes a `accountIsPaying` key, because the flag describes an account rather than a
        // visit. The dimension's $suggestedValuesCallback answers instead, and it is consulted
        // before the visits log is queried at all -- so this passes with no tracked data and no
        // look-back window.
        $this->assertSame(['0', '1'], $values);
    }

    public function testContributesATranslationKeyRatherThanATranslatedString(): void
    {
        $translations = [];

        (new ExampleLogTables())->addMetricTranslations($translations);

        // The event takes keys. Core maps translate() over the whole array after posting it, so a
        // handler that translated for itself would translate twice -- harmless in English, where the
        // second pass finds no key and returns the text unchanged, and a missing name in every other
        // language. Asserting the key rather than the name is the only way to see the difference.
        $this->assertSame(
            [PayingAccountVisits::NB_VISITS_PAYING_ACCOUNT_RECORD => 'ExampleLogTables_NbVisitsPayingAccount'],
            $translations
        );
    }

    public function testTheArchivedMetricIsNamedRatherThanShownUnderItsRecordName(): void
    {
        $translations = Metrics::getDefaultMetricTranslations();

        $this->assertArrayHasKey(PayingAccountVisits::NB_VISITS_PAYING_ACCOUNT_RECORD, $translations);

        // And what core hands on is the translated name, because it translates the array after
        // posting the event. Without the subscription the metric would appear under its record name.
        $this->assertSame(
            'Visits by paying accounts',
            $translations[PayingAccountVisits::NB_VISITS_PAYING_ACCOUNT_RECORD]
        );
    }
}

SegmentAndMetricNamesTest::$fixture = new VisitsWithUserIdAndCustomData();

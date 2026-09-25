<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Fixtures;

use Piwik\Access;
use Piwik\Config;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorApi;
use Piwik\Tests\Framework\Fixture;

/**
 * One website carrying enough stored segments to make the Custom Reports promotion fire.
 *
 * The segments trigger is the only one that reads no archived report at all, so a fixture
 * for it needs no tracked visits, no archiving and no fixed reporting week - which is what
 * makes it the right promotion to pin the banner's appearance and its links against. Every
 * other promotion would drag a week of seeded traffic behind it, and that data would fall
 * out of the reporting window the moment the test's frozen date stopped matching.
 *
 * It also sits at priority 1, so nothing in the registry can outrank it and change which
 * promotion the dashboard renders.
 */
class SiteWithFiveSegments extends Fixture
{
    public $idSite = 1;

    public $dateTime = '2026-01-10 09:00:00';

    /**
     * One more than {@see \Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SegmentsTrigger::MINIMUM_SEGMENTS}
     * requires, so the promotion does not sit exactly on its threshold.
     */
    public const SEGMENTS = [
        'Visitors from Japan' => 'countryCode==jp',
        'Mobile visitors' => 'deviceType==smartphone',
        'Returning visitors' => 'visitorType==returning',
        'Long visits' => 'visitDuration>=300',
        'Search engine traffic' => 'referrerType==search',
        'Direct entries' => 'referrerType==direct',
    ];

    public function setUp(): void
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite('2026-01-01 00:00:00');
        }

        // Storing a segment would otherwise queue archiving work this fixture has no use for.
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;

        // Storing a segment for all users is a super user action, and a fixture has no
        // logged-in user of its own.
        Access::doAsSuperUser(function () {
            foreach (self::SEGMENTS as $name => $definition) {
                SegmentEditorApi::getInstance()->add($name, $definition, $this->idSite, true, true);
            }

            // The promotion under test only fires above a segment count, so a fixture that
            // quietly stored fewer would produce a confusing "banner never appeared".
            $stored = count(SegmentEditorApi::getInstance()->getAll($this->idSite));

            if ($stored < count(self::SEGMENTS)) {
                throw new \Exception(sprintf(
                    'SiteWithFiveSegments stored only %d of %d segments',
                    $stored,
                    count(self::SEGMENTS)
                ));
            }
        });

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;

        $this->trackOneVisit();
    }

    /**
     * A website with no data at all never reaches the dashboard: Matomo sends it to the
     * tracking-code page instead, and the banner under test is never rendered. One visit
     * is enough, and the segments trigger reads no report, so nothing here has to be
     * archived or fall inside a particular reporting week.
     */
    private function trackOneVisit(): void
    {
        $tracker = self::getTracker($this->idSite, $this->dateTime, true, true);
        $tracker->setUrl('http://example.org/');
        self::checkResponse($tracker->doTrackPageView('Home'));
    }

    public function tearDown(): void
    {
        // nothing to undo: the test database is rebuilt for the fixture
    }
}

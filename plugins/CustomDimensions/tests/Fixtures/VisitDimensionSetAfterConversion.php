<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CustomDimensions\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsApi;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Tests\Framework\Fixture;

/**
 * Three visits that all end with the same visit-scoped custom dimension value and all convert the
 * same goal. They differ only in the order of the two events inside the visit.
 *
 * Two of them set the dimension before converting, so the conversion carries the value. The third
 * converts first and is only tagged afterwards, so its conversion carries no value at all, while the
 * visit still ends up under the tagged value because a visit-scoped dimension keeps the last one
 * recorded.
 *
 * Reproduces the customer report behind dev-20706, where a Custom Dimension report showed 2
 * conversions for a value whose 43 visits held 3.
 */
class VisitDimensionSetAfterConversion extends Fixture
{
    public $idSite = 1;

    public $dateTime = '2026-08-17 03:00:00';

    /** Allocated by configureNewCustomDimension(), the first one on a fresh site. */
    public $idDimension = 1;

    public $idGoal = 1;

    public const DIMENSION_NAME = 'Chatbot-Nutzung';

    public const DIMENSION_VALUE = 'Chatbot geoeffnet';

    public const GOAL_NAME = 'Bestellung';

    /** Every conversion in this fixture comes from a page view whose URL contains this. */
    public const GOAL_PATTERN = 'bestellung';

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsite(): void
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite($this->dateTime);
        }

        CustomDimensionsApi::getInstance()->configureNewCustomDimension(
            $this->idSite,
            self::DIMENSION_NAME,
            CustomDimensions::SCOPE_VISIT,
            $active = true
        );

        GoalsApi::getInstance()->addGoal(
            $this->idSite,
            self::GOAL_NAME,
            'url',
            self::GOAL_PATTERN,
            'contains'
        );
    }

    private function trackVisits(): void
    {
        // the two visits the customer sees counted: tagged, then converting
        $this->trackTaggedThenConverted('11.22.33.44', 1);
        $this->trackTaggedThenConverted('11.22.33.45', 2);

        // the one that goes missing: converting, then tagged
        $this->trackConvertedThenTagged('11.22.33.46', 3);
    }

    /**
     * The dimension is already on the visit when the goal converts, so the conversion carries it.
     */
    private function trackTaggedThenConverted(string $ip, int $hourOffset): void
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp($ip);

        $t->setCustomDimension($this->idDimension, self::DIMENSION_VALUE);
        $t->setForceVisitDateTime($this->at($hourOffset, 0));
        $t->setUrl('http://example.org/chatbot');
        self::checkResponse($t->doTrackPageView('Chatbot geoeffnet'));

        $t->setForceVisitDateTime($this->at($hourOffset, 5));
        $t->setUrl('http://example.org/' . self::GOAL_PATTERN);
        self::checkResponse($t->doTrackPageView('Bestellung abgeschlossen'));
    }

    /**
     * The goal converts before the dimension is ever sent, so the conversion carries no value. The
     * visit is tagged on the next request and therefore still ends under the tagged value.
     */
    private function trackConvertedThenTagged(string $ip, int $hourOffset): void
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setIp($ip);

        $t->setForceVisitDateTime($this->at($hourOffset, 0));
        $t->setUrl('http://example.org/' . self::GOAL_PATTERN);
        self::checkResponse($t->doTrackPageView('Bestellung abgeschlossen'));

        $t->setCustomDimension($this->idDimension, self::DIMENSION_VALUE);
        $t->setForceVisitDateTime($this->at($hourOffset, 5));
        $t->setUrl('http://example.org/chatbot');
        self::checkResponse($t->doTrackPageView('Chatbot geoeffnet'));
    }

    private function at(int $hours, int $minutes): string
    {
        return Date::factory($this->dateTime)
            ->addHour($hours)
            ->addPeriod($minutes, 'minute')
            ->getDatetime();
    }
}

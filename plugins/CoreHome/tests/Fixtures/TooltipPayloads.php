<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Fixtures;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsApi;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Tests\Framework\Fixture;

/**
 * Tracks one visit per payload class, carrying the payload in every field that ends up in a tooltip.
 *
 * A tooltip reads its content back from a `title` attribute the browser has already decoded and
 * renders it as HTML, so a tracked value has to survive two parsers to be displayed rather than
 * rendered. The classes below are the shapes that decide whether it does: markup a tooltip may
 * render, markup carrying attributes, shapes an HTML parser discards, and text whose bytes make
 * escaping or decoding behave differently.
 *
 * Fields differ in how deeply they are escaped by the time a template sees them, so the same payload
 * is tracked through several of them. The site search keyword is tracked twice on purpose: as its own
 * parameter it arrives escaped, while a keyword taken from the page URL arrives raw, because that
 * path url decodes it after the tracker has sanitised the request.
 */
class TooltipPayloads extends Fixture
{
    public $dateTime = '2019-08-09 11:22:33';

    /**
     * An id no other fixture creates, so combining this fixture with others cannot mix its payloads
     * into a site another fixture reports on.
     */
    public $idSite = 20;

    /**
     * Each class is tracked into every field below. Keep the keys short: they are what a failing
     * assertion names.
     */
    public static function getPayloads(): array
    {
        return [
            'plain'      => 'PL1 plain value',
            'inline'     => 'PL2 <b>bold</b> <em>em</em>',
            'attribute'  => 'PL3 <img src=x onerror=window.__tooltipProbe=1>',
            'carrier'    => 'PL4 <div class=dataTable data-table-type=JqplotGraph data-report=Referrers.getWebsites>y</div>',
            'entities'   => 'PL5 A & B "q" <3 &amp; &lt;b&gt;',
            'discarded'  => 'PL6 x</span>y <?z>w A<B',
            'unbalanced' => 'PL7 <svg><style>*{display:none}</style></svg> t<b',
            'cyrillic'   => 'PL8 привет мир <b>ж</b>',
            'chinese'    => 'PL9 统计分析 <b>数</b>',
            'emoji'      => 'PL10 hits 😀🔥 <b>x</b>',
            'combining'  => "PL11 e\u{0301}le\u{0300}ve <b>a</b>",
            'percent'    => 'PL12 a+b %41 %2F <b>c</b>',
        ];
    }

    /**
     * Referrer urls are parsed before they are stored, so their payloads carry no spaces or quotes.
     */
    public static function getReferrerPayloads(): array
    {
        return [
            'plain'      => 'PL1-plain',
            'inline'     => 'PL2-<b>bold</b>',
            'attribute'  => 'PL3-<img/src=x/onerror=window.__tooltipProbe=1>',
            'carrier'    => 'PL4-<div/class=dataTable/data-table-type=JqplotGraph>y</div>',
            'discarded'  => 'PL6-x</span>y<?z>wA<B',
            'unbalanced' => 'PL7-<svg><style>*{display:none}</style></svg>t<b',
            'emoji'      => 'PL10-😀🔥',
        ];
    }

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->setUpGoal();
        $this->setUpCustomDimensions();
        $this->trackPayloadVisits();
        $this->trackReferrerVisits();
        $this->trackVisitWithBrowserPlugins();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsite(): void
    {
        if (self::siteCreated($this->idSite)) {
            return;
        }

        Db::query('ALTER TABLE ' . Common::prefixTable('site') . ' AUTO_INCREMENT = ' . (int) $this->idSite);

        // ecommerce on for the order tooltips, site search on so a keyword in a page url is detected
        $idSite = self::createWebsite($this->dateTime, 1, 'Tooltip payloads', 'http://example.org', 1, 'q');

        if ($idSite != $this->idSite) {
            throw new \Exception("Expected the tooltip payload site to get id {$this->idSite}, got $idSite.");
        }
    }

    /**
     * The goal name reaches the action tooltip of a conversion, so it carries a payload too.
     */
    private function setUpGoal(): void
    {
        if (!self::goalExists($this->idSite, 1)) {
            GoalsApi::getInstance()->addGoal(
                $this->idSite,
                'goal ' . self::getPayloads()['inline'],
                'manually',
                '',
                'contains'
            );
        }
    }

    /**
     * Custom dimension values reach the action tooltip as well, one per scope.
     */
    private function setUpCustomDimensions(): void
    {
        $configured = CustomDimensionsApi::getInstance()->getConfiguredCustomDimensions($this->idSite);

        if (count($configured) > 0) {
            return;
        }

        CustomDimensionsApi::getInstance()->configureNewCustomDimension($this->idSite, 'visit dimension', 'visit', true);
        CustomDimensionsApi::getInstance()->configureNewCustomDimension($this->idSite, 'action dimension', 'action', true);
    }

    /**
     * One visit per class, carrying the payload in the page url and title, an outlink, a download,
     * a site search, an event, an ecommerce item, a content block, a conversion, two custom dimensions
     * and the user id.
     */
    private function trackPayloadVisits(): void
    {
        $hour = 0;

        foreach (self::getPayloads() as $name => $payload) {
            $hour += 1;

            $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
            $t->setTokenAuth(self::getTokenAuth());
            $t->setVisitorId(substr(sha1($name), 0, 16));
            $t->setUserId('uid ' . $payload);
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($hour)->getDatetime());

            $t->setCustomDimension(1, 'visit dim ' . $payload);
            $t->setCustomDimension(2, 'action dim ' . $payload);

            $t->setUrl('http://example.org/page/' . $payload);
            self::checkResponse($t->doTrackPageView('title ' . $payload));

            self::checkResponse($t->doTrackAction('http://out.example/o/' . $payload, 'link'));
            self::checkResponse($t->doTrackAction('http://dl.example/d/' . $payload, 'download'));

            // as its own parameter the keyword is sanitised, so a template sees it escaped
            self::checkResponse($t->doTrackSiteSearch($payload, '', 3));

            self::checkResponse($t->doTrackEvent('cat ' . $payload, 'act ' . $payload, 'name ' . $payload));

            $t->addEcommerceItem('sku ' . $payload, 'name ' . $payload, 'cat ' . $payload, 4.99, 2);
            self::checkResponse($t->doTrackEcommerceOrder('order-' . $name, 9.99, 9.98, 0, 0.01, 0));

            self::checkResponse($t->doTrackContentImpression('content ' . $payload, 'piece ' . $payload, 'http://example.org/target/' . $payload));
            self::checkResponse($t->doTrackContentInteraction('click ' . $payload, 'content ' . $payload, 'piece ' . $payload, 'http://example.org/target/' . $payload));

            self::checkResponse($t->doTrackGoal(1, 12.34));
        }

        $this->trackSearchKeywordsFromPageUrl($hour + 1);
    }

    /**
     * The other keyword path: Matomo takes the keyword from the page url and url decodes it after the
     * request was sanitised, so an encoded `<` ends up stored as a literal one.
     */
    private function trackSearchKeywordsFromPageUrl(int $hour): void
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->setVisitorId(substr(sha1('keyword-from-url'), 0, 16));
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($hour)->getDatetime());

        foreach (['inline', 'attribute', 'unbalanced', 'emoji'] as $name) {
            $payload = self::getPayloads()[$name];

            $t->setUrl('http://example.org/search?q=' . rawurlencode($payload));
            self::checkResponse($t->doTrackPageView('search from url: ' . $name));
        }
    }

    /**
     * Referrer urls live on the visit, so each one needs a visit of its own.
     */
    private function trackReferrerVisits(): void
    {
        $hour = 20;

        foreach (self::getReferrerPayloads() as $name => $payload) {
            $hour += 1;

            $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
            $t->setTokenAuth(self::getTokenAuth());
            $t->setVisitorId(substr(sha1('ref-' . $name), 0, 16));
            $t->setForceNewVisit();
            $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($hour)->getDatetime());
            $t->setUrlReferrer('http://referrer.example/r/' . $payload);

            $t->setUrl('http://example.org/landing/' . $name);
            self::checkResponse($t->doTrackPageView('landing ' . $name));
        }
    }

    /**
     * The icon tooltips in the visits log clone a hidden list instead of reading a title, and the
     * plugins entry of that list is the one tooltip that legitimately contains images.
     */
    private function trackVisitWithBrowserPlugins(): void
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->setVisitorId(substr(sha1('plugins'), 0, 16));
        $t->setForceNewVisit();
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(30)->getDatetime());
        $t->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        $t->setPlugins(true, true, true, true, true, true, true);

        $t->setUrl('http://example.org/plugins-probe');
        self::checkResponse($t->doTrackPageView('plugins probe'));
    }
}

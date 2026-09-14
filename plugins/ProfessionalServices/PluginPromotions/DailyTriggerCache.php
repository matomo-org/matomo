<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Date;
use Piwik\Option;
use Piwik\Site;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\TriggerResult;

/**
 * Remembers the outcome of a report based trigger for one website for one day.
 *
 * Both positive and negative outcomes are stored, so a website that does not qualify is
 * not re-evaluated on every dashboard request. Only the trigger outcome is cached, never
 * the selected promotion: cooldowns, trial state and permissions change independently of
 * the reports, so no entry ever needs invalidating when a user dismisses something.
 *
 * `Option` is used rather than the lazy cache because the entries must survive cache
 * flushes and plugin activation, and can be purged per website when a site is deleted.
 */
class DailyTriggerCache
{
    public const OPTION_PREFIX = 'ProfessionalServices.PromotionTrigger.';

    public function getOrEvaluate(string $triggerName, int $idSite, callable $evaluate): TriggerResult
    {
        $today = $this->getToday($idSite);
        $cached = $this->load($triggerName, $idSite);

        if (!empty($cached) && ($cached['evaluationDate'] ?? null) === $today) {
            return TriggerResult::fromArray($cached);
        }

        /** @var TriggerResult $result */
        $result = $evaluate();

        $this->store($triggerName, $idSite, $today, $result);

        return $result;
    }

    /**
     * Removes every cached trigger outcome for a website. The site id is the last part of
     * the option name, so a single pattern covers all triggers.
     */
    public static function deleteForSite(int $idSite): void
    {
        Option::deleteLike(self::OPTION_PREFIX . '%.' . $idSite);
    }

    public static function getOptionName(string $triggerName, int $idSite): string
    {
        return self::OPTION_PREFIX . $triggerName . '.' . $idSite;
    }

    /**
     * @return array<string, mixed>
     */
    private function load(string $triggerName, int $idSite): array
    {
        $value = Option::get(self::getOptionName($triggerName, $idSite));
        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function store(string $triggerName, int $idSite, string $today, TriggerResult $result): void
    {
        $value = array_merge(['evaluationDate' => $today], $result->toArray());

        Option::set(self::getOptionName($triggerName, $idSite), json_encode($value), $autoload = 0);
    }

    /**
     * Today in the website's own timezone.
     *
     * It has to be the website's, because what is cached is an answer about a reporting
     * period that {@see ReportPeriod} resolves in that timezone. Keyed on a UTC date, a
     * result computed either side of the website's local midnight could be served for a
     * day on which it describes the previous week.
     */
    private function getToday(int $idSite): string
    {
        try {
            $timezone = Site::getTimezoneFor($idSite);
        } catch (\Throwable $e) {
            // A website that cannot be looked up has no timezone to prefer, and failing to
            // read a cache key is never worth an exception. UTC keys the entry the way it
            // was keyed before.
            $timezone = 'UTC';
        }

        return Date::factory(Date::getNowTimestamp(), $timezone)->toString();
    }
}

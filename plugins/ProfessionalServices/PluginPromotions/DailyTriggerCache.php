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

    /**
     * Every trigger's entry for a website, by website, read once per website per request.
     *
     * @var array<int, array<string, array<string, mixed>>>
     */
    private array $loaded = [];

    public function getOrEvaluate(string $triggerName, int $idSite, callable $evaluate): TriggerResult
    {
        $today = $this->getToday($idSite);
        $cached = $this->load($triggerName, $idSite);

        if (!empty($cached) && ($cached['evaluationDate'] ?? null) === $today) {
            return TriggerResult::fromArray($cached);
        }

        try {
            /** @var TriggerResult $result */
            $result = $evaluate();
        } catch (\Throwable $e) {
            // A trigger that fails is cached as "did not fire" before the failure is passed
            // on. Without this a reliably broken trigger - a missing plugin, a report that
            // throws - repeats its archive reads on every dashboard request for every
            // user, forever, while being logged only at debug level. Storing the negative
            // bounds that to once per website per day, which is what the cache is for.
            $this->store($triggerName, $idSite, $today, TriggerResult::notTriggered());

            throw $e;
        }

        // A provisional answer is one the trigger could not settle yet, because the
        // reports it reads are still being archived. Remembering it would keep the
        // promotion hidden until midnight even though archiving finished minutes later.
        if (!$result->isProvisional()) {
            $this->store($triggerName, $idSite, $today, $result);
        }

        return $result;
    }

    /**
     * Removes every cached trigger outcome for a website. The site id leads the option
     * name, so a single pattern covers all triggers and matches on the name's literal part.
     *
     * An instance method rather than a static one, so that it can drop what it deletes from
     * the batch read below: a cache that remembers rows it has just deleted is worse than
     * no cache at all.
     */
    public function deleteForSite(int $idSite): void
    {
        Option::deleteLike(self::OPTION_PREFIX . $idSite . '.%');

        unset($this->loaded[$idSite]);
    }

    /**
     * The website comes before the trigger, so that a pattern for one website is a literal
     * prefix. `option_name` is the table's primary key, which makes {@see loadForSite()} an
     * index range over this website's dozen entries rather than over every website's.
     */
    public static function getOptionName(string $triggerName, int $idSite): string
    {
        return self::OPTION_PREFIX . $idSite . '.' . $triggerName;
    }

    /**
     * @return array<string, mixed>
     */
    private function load(string $triggerName, int $idSite): array
    {
        if (!array_key_exists($idSite, $this->loaded)) {
            $this->loaded[$idSite] = $this->loadForSite($idSite);
        }

        return $this->loaded[$idSite][self::getOptionName($triggerName, $idSite)] ?? [];
    }

    /**
     * Every trigger's entry for one website, in one query.
     *
     * Twelve triggers are cached here, and a dashboard walks the whole ladder whenever
     * none of them fires - which is the ordinary case. `Option::get()` issues a query per
     * name it has not already read, so reading them one at a time was twelve round trips
     * for what is one set of rows under a shared prefix.
     *
     * @return array<string, array<string, mixed>>
     */
    private function loadForSite(int $idSite): array
    {
        $entries = [];

        foreach (Option::getLike(self::OPTION_PREFIX . $idSite . '.%') as $name => $value) {
            $decoded = json_decode((string) $value, true);

            $entries[$name] = is_array($decoded) ? $decoded : [];
        }

        return $entries;
    }

    private function store(string $triggerName, int $idSite, string $today, TriggerResult $result): void
    {
        $value = array_merge(['evaluationDate' => $today], $result->toArray());
        $name = self::getOptionName($triggerName, $idSite);

        Option::set($name, json_encode($value), $autoload = 0);

        // The website's entries are read once and then answered from memory, so an entry
        // written afterwards has to join them. Without this, a second ask for the same
        // trigger in one request would evaluate it again, which is the cost this cache
        // exists to avoid.
        if (array_key_exists($idSite, $this->loaded)) {
            $this->loaded[$idSite][$name] = $value;
        }
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

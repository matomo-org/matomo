<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tracker;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Plugins\UserCountry\Columns\Base;

trait RequestHandlerTrait
{
    protected $fieldsThatRequireAuth = [
        'city',
        'region',
        'country',
        'lat',
        'long',
    ];

    protected function checkSiteExists(Request $request): void
    {
        try {
            $request->getIdSite();
        } catch (UnexpectedWebsiteFoundException $e) {
            $idSite = $request->getRawParams()['idsite'] ?? null;
            if (is_numeric($idSite) && (string)(int)$idSite === (string)$idSite && (int)$idSite >= 0) {
                // only log a failure in case the provided idsite was valid positive integer
                StaticContainer::get(Failures::class)->logFailure(Failures::FAILURE_ID_INVALID_SITE, $request);
            }

            throw $e;
        }
    }

    protected function validateRequest(Request $request): void
    {
        // Check for params that aren't allowed to be included unless the request is authenticated
        foreach ($this->fieldsThatRequireAuth as $field) {
            Base::getValueFromUrlParamsIfAllowed($field, $request);
        }

        // Special logic for timestamp as some overrides are OK without auth and others aren't
        $request->getCurrentTimestamp();
    }

    protected function markArchivedReportsAsInvalidIfArchiveAlreadyFinished(Request $request): void
    {
        $idSite = (int)$request->getIdSite();
        $time   = $request->getCurrentTimestamp();

        $timezone = $this->getTimezoneForSite($idSite);

        if (!isset($timezone)) {
            return;
        }

        $date = Date::factory((int)$time, $timezone);

        // $date->isToday() is buggy when server and website timezones don't match - so we'll do our own checking
        $startOfToday         = Date::factoryInTimezone('yesterday', $timezone)->addDay(1);
        $isLaterThanYesterday = $date->getTimestamp() >= $startOfToday->getTimestamp();
        if ($isLaterThanYesterday) {
            return; // don't try to invalidate archives for today or later
        }

        StaticContainer::get('Piwik\Archive\ArchiveInvalidator')->rememberToInvalidateArchivedReportsLater($idSite, $date);
    }

    private function getTimezoneForSite(int $idSite): ?string
    {
        try {
            $site = Cache::getCacheWebsiteAttributes($idSite);
        } catch (UnexpectedWebsiteFoundException $e) {
            return null;
        }

        if (!empty($site['timezone'])) {
            return $site['timezone'];
        }

        return null;
    }
}

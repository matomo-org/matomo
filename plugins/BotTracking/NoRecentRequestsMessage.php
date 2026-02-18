<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

use Piwik\Date;
use Piwik\Period;
use Piwik\Site;
use Piwik\Plugins\BotTracking\BotDetector;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;

class NoRecentRequestsMessage
{
    public static function shouldShow(int $idSite, string $period, string $date): bool
    {
        if ($idSite <= 0 || $period === '' || $date === '') {
            return false;
        }

        try {
            $selected = Period\Factory::build($period, $date);
        } catch (\Exception $e) {
            return false;
        }

        $siteTimezone = Site::getTimezoneFor($idSite);
        $todayLocal   = Date::factoryInTimezone('today', $siteTimezone)
            ->setTimezone($siteTimezone);

        $last7Start    = $todayLocal->subDay(6);
        $last7End      = $todayLocal->getEndOfDay();
        $selectedStart = $selected->getDateStart()->setTimezone($siteTimezone);
        $selectedEnd   = $selected->getDateEnd()->getEndOfDay()->setTimezone($siteTimezone);

        $overlapsLast7 = !$selectedEnd->isEarlier($last7Start)
            && !$selectedStart->isLater($last7End);

        if (!$overlapsLast7) {
            return false;
        }

        // server_time is stored in UTC and is indexed with idsite, so querying the latest value is fast.
        $lastRequest = (new BotRequestsDao())->getLastServerTimeForSiteAndBotType(
            $idSite,
            BotDetector::BOT_TYPE_AI_CHATBOT
        );

        if (empty($lastRequest)) {
            return true;
        }

        // Date::factory interprets the stored UTC string, so comparison is done in UTC timestamps.
        return Date::factory($lastRequest)->isEarlier($last7Start);
    }
}

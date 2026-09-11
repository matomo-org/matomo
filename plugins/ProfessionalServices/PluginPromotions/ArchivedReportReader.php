<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Archive;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Period;
use Piwik\Segment;
use Piwik\Site;

/**
 * Reads existing archived report data for the promotion triggers without ever building an
 * archive.
 *
 * Opening a dashboard must never be the reason an archive gets created, and there is
 * exactly one supported way to guarantee that: `Archive::forceFetchingWithoutLaunchingArchiving()`,
 * which is the only condition besides `Rules::isArchivingEnabledFor()` that
 * `Archive::getArchiveIds()` looks at. Use {@see buildArchive()} whenever the data can be
 * read straight from a record.
 *
 * A report that can only be reached through a plugin API method cannot use that opt-out,
 * because the API builds its own archive internally. For those, call
 * {@see hasCompletedArchive()} first: when a completed archive already exists the request
 * reuses it, and when it does not the trigger reports no result for the day rather than
 * making the dashboard wait for archiving.
 *
 * Note that the `Archiving.isRequestAuthorizedToArchive` event is not usable here. It is
 * only posted from `Rules::isRequestAuthorizedToArchive()` when parameters are passed, and
 * the call that decides whether archiving runs (`Rules::isArchivingEnabledFor()`) passes
 * none.
 */
class ArchivedReportReader
{
    /**
     * Builds an archive query that is guaranteed not to launch archiving.
     */
    public function buildArchive(int $idSite, string $period, string $date): Archive
    {
        /** @var Archive $archive */
        $archive = Archive::build($idSite, $period, $date);
        $archive->forceFetchingWithoutLaunchingArchiving();

        return $archive;
    }

    /**
     * Whether a completed archive for the given plugin and period already exists, so that
     * requesting one of its reports reuses it instead of building it.
     *
     * Only fully completed archives count. An invalidated or temporary one may be rebuilt
     * when it is requested, which is exactly what must not happen here.
     */
    public function hasCompletedArchive(int $idSite, string $pluginName, Period $period): bool
    {
        $parameters = new Parameters(new Site($idSite), $period, new Segment('', [$idSite]));
        $parameters->setRequestedPlugin($pluginName);

        $archiveInfo = ArchiveSelector::getArchiveIdAndVisits($parameters, false, false);

        return !empty($archiveInfo['idArchives'])
            && (int) $archiveInfo['doneFlagValue'] === ArchiveWriter::DONE_OK;
    }
}

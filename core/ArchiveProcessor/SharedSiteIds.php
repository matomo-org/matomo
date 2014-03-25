<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ArchiveProcessor;

use Exception;
use Piwik\Option;
use Piwik\CliMulti\Process;

class SharedSiteIds
{
    private $siteIds = array();
    private $currentSiteId;

    public function __construct($websiteIds)
    {
        $self = $this;
        $this->siteIds = $this->runExclusive(function () use ($self, $websiteIds) {
            $existingWebsiteIds = $self->getAllSiteIdsToArchive();

            if (!empty($existingWebsiteIds)) {
                return $existingWebsiteIds;
            }

            $self->setSiteIdsToArchive($websiteIds);

            return $websiteIds;
        });
    }

    public function getNumSites()
    {
        return count($this->siteIds);
    }

    public function getNumProcessedWebsites()
    {
        if (empty($this->currentSiteId)) {
            return 0;
        }

        $index = array_search($this->currentSiteId, $this->siteIds);

        if (false === $index) {
            return 0;
        }

        return $index + 1;
    }

    public function setSiteIdsToArchive($siteIds)
    {
        if (!empty($siteIds)) {
            Option::set('SiteIdsToArchive', implode(',', $siteIds));
        } else {
            Option::delete('SiteIdsToArchive');
        }
    }

    public function getAllSiteIdsToArchive()
    {
        Option::clearCachedOption('SiteIdsToArchive');
        $siteIdsToArchive = Option::get('SiteIdsToArchive');

        if (empty($siteIdsToArchive)) {
            return array();
        }

        return explode(',', trim($siteIdsToArchive));
    }

    /**
     * If there are multiple archiver running on the same node it makes sure only one of them performs an action and it
     * will wait until another one has finished. Any closure you pass here should be very fast as other processes wait
     * for this closure to finish otherwise. Currently only used for making multiple archivers at the same time work.
     * If a closure takes more than 5 seconds we assume it is dead and simply continue.
     *
     * @param \Closure $closure
     * @return mixed
     * @throws \Exception
     */
    private function runExclusive($closure)
    {
        $process = new Process('archive.lock');
        while ($process->isRunning() && $process->getSecondsSinceCreation() < 5) {
            // wait max 5 seconds, such an operation should not take longer
            usleep(25);
        }

        $process->startProcess();

        try {
            $result = $closure();
        } catch (Exception $e) {
            $process->finishProcess();
            throw $e;
        }

        $process->finishProcess();

        return $result;
    }

    public function getNextSiteId()
    {
        $self = $this;

        $this->currentSiteId = $this->runExclusive(function () use ($self) {

            $siteIds = $self->getAllSiteIdsToArchive();

            if (empty($siteIds)) {
                return null;
            }

            $nextSiteId = array_shift($siteIds);
            $self->setSiteIdsToArchive($siteIds);

            return $nextSiteId;
        });

        return $this->currentSiteId;
    }

    public static function isSupported()
    {
        return Process::isSupported();
    }

}


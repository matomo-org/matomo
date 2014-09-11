<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\CronArchive;

use Exception;
use Piwik\CliMulti\Process;
use Piwik\Option;

/**
 * This class saves all to be processed siteIds in an Option named 'SharedSiteIdsToArchive' and processes all sites
 * within that list. If a user starts multiple archiver those archiver will help to finish processing that list.
 */
class SharedSiteIds
{
    private $siteIds = array();
    private $currentSiteId;
    private $done = false;

    public function __construct($websiteIds)
    {
        if (empty($websiteIds)) {
            $websiteIds = array();
        }

        $self = $this;
        $this->siteIds = $this->runExclusive(function () use ($self, $websiteIds) {
            // if there are already sites to be archived registered, prefer the list of existing archive, meaning help
            // to finish this queue of sites instead of starting a new queue
            $existingWebsiteIds = $self->getAllSiteIdsToArchive();

            if (!empty($existingWebsiteIds)) {
                return $existingWebsiteIds;
            }

            $self->setSiteIdsToArchive($websiteIds);

            return $websiteIds;
        });
    }

    public function getInitialSiteIds()
    {
        return $this->siteIds;
    }

    /**
     * Get the number of total websites that needs to be processed.
     *
     * @return int
     */
    public function getNumSites()
    {
        return count($this->siteIds);
    }

    /**
     * Get the number of already processed websites (not necessarily all of those where processed by this archiver).
     *
     * @return int
     */
    public function getNumProcessedWebsites()
    {
        if ($this->done) {
            return $this->getNumSites();
        }

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
            Option::set('SharedSiteIdsToArchive', implode(',', $siteIds));
        } else {
            Option::delete('SharedSiteIdsToArchive');
        }
    }

    public function getAllSiteIdsToArchive()
    {
        Option::clearCachedOption('SharedSiteIdsToArchive');
        $siteIdsToArchive = Option::get('SharedSiteIdsToArchive');

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
        $process = new Process('archive.sharedsiteids');

        while ($process->isRunning() && $process->getSecondsSinceCreation() < 5) {
            // wait max 5 seconds, such an operation should not take longer
            usleep(25 * 1000);
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

    /**
     * Get the next site id that needs to be processed or null if all site ids where processed.
     *
     * @return int|null
     */
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

        if (is_null($this->currentSiteId)) {
            $this->done = true;
        }

        return $this->currentSiteId;
    }

    public static function isSupported()
    {
        return Process::isSupported();
    }

}


<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\CronArchive;


class FixedSiteIds
{
    private $siteIds = array();
    private $index   = -1;

    public function __construct($websiteIds)
    {
        if (!empty($websiteIds)) {
            $this->siteIds = $websiteIds;
        }
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
     * Get the number of already processed websites. All websites were processed by the current archiver.
     *
     * @return int
     */
    public function getNumProcessedWebsites()
    {
        $numProcessed = $this->index + 1;

        if ($numProcessed > $this->getNumSites()) {
            return $this->getNumSites();
        }

        return $numProcessed;
    }

    public function getNextSiteId()
    {
        $this->index++;

        if (!empty($this->siteIds[$this->index])) {
            return $this->siteIds[$this->index];
        }

        return null;
    }
}

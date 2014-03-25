<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ArchiveProcessor;

use Piwik\CronArchive;
use Exception;
use Piwik\Option;
use Piwik\CliMulti\Process;

class FixedSiteIds
{
    private $siteIds = array();
    private $index   = -1;

    public function __construct($websiteIds)
    {
        $this->siteIds = $websiteIds;
    }

    public function getNumSites()
    {
        return count($this->siteIds);
    }

    public function getNumProcessedWebsites()
    {
        return $this->index + 1;
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


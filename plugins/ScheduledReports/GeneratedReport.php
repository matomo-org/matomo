<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ScheduledReports;

use Piwik\Piwik;

class GeneratedReport
{
    /**
     * @var array
     */
    private $reportDetails;

    /**
     * @var string
     */
    private $reportTitle;

    /**
     * @var string
     */
    private $prettyDate;

    /**
     * @var string
     */
    private $contents;

    /**
     * @var array
     */
    private $additionalFiles;

    public function __construct(array $reportDetails, $reportTitle, $prettyDate, $contents, array $additionalFiles)
    {
        $this->reportDetails = $reportDetails;
        $this->reportTitle = $reportTitle;
        $this->prettyDate = $prettyDate;
        $this->contents = $contents;
        $this->additionalFiles = $additionalFiles;
    }

    /**
     * @return array
     */
    public function getReportDetails()
    {
        return $this->reportDetails;
    }

    /**
     * @return string
     */
    public function getReportTitle()
    {
        return $this->reportTitle;
    }

    /**
     * @return string
     */
    public function getPrettyDate()
    {
        return $this->prettyDate;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @return array
     */
    public function getAdditionalFiles()
    {
        return $this->additionalFiles;
    }

    /**
     * @return string
     */
    public function getReportFormat()
    {
        return $this->reportDetails['format'] ?: 'pdf';
    }

    /**
     * @return string
     */
    public function getReportDescription()
    {
        return Piwik::translate('General_Report') . ' ' . $this->reportTitle . " - " . $this->prettyDate;
    }
}

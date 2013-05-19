<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_MobileMessaging_ReportRenderer
 */

/**
 *
 * @package Piwik_MobileMessaging_ReportRenderer
 */
class Piwik_MobileMessaging_ReportRenderer_Exception extends Piwik_ReportRenderer
{
    private $rendering = "";

    function __construct($exception)
    {
        $this->rendering = $exception;
    }

    public function setLocale($locale)
    {
        // nothing to do
    }

    public function sendToDisk($filename)
    {
        return Piwik_ReportRenderer::writeFile(
            $filename,
            Piwik_MobileMessaging_ReportRenderer_Sms::SMS_FILE_EXTENSION,
            $this->rendering
        );
    }

    public function sendToBrowserDownload($filename)
    {
        Piwik_ReportRenderer::sendToBrowser(
            $filename,
            Piwik_MobileMessaging_ReportRenderer_Sms::SMS_FILE_EXTENSION,
            Piwik_MobileMessaging_ReportRenderer_Sms::SMS_CONTENT_TYPE,
            $this->rendering
        );
    }

    public function sendToBrowserInline($filename)
    {
        Piwik_ReportRenderer::inlineToBrowser(
            Piwik_MobileMessaging_ReportRenderer_Sms::SMS_CONTENT_TYPE,
            $this->rendering
        );
    }

    public function getRenderedReport()
    {
        return $this->rendering;
    }

    public function renderFrontPage($reportTitle, $prettyDate, $description, $reportMetadata, $segment)
    {
        // nothing to do
    }

    public function renderReport($processedReport)
    {
        // nothing to do
    }
}

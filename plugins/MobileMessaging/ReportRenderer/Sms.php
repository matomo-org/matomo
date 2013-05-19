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
class Piwik_MobileMessaging_ReportRenderer_Sms extends Piwik_ReportRenderer
{
    const FLOAT_REGEXP = '/[-+]?[0-9]*[\.,]?[0-9]+/';
    const SMS_CONTENT_TYPE = 'text/plain';
    const SMS_FILE_EXTENSION = 'sms';

    private $rendering = "";

    public function setLocale($locale)
    {
        // nothing to do
    }

    public function sendToDisk($filename)
    {
        return Piwik_ReportRenderer::writeFile($filename, self::SMS_FILE_EXTENSION, $this->rendering);
    }

    public function sendToBrowserDownload($filename)
    {
        Piwik_ReportRenderer::sendToBrowser($filename, self::SMS_FILE_EXTENSION, self::SMS_CONTENT_TYPE, $this->rendering);
    }

    public function sendToBrowserInline($filename)
    {
        Piwik_ReportRenderer::inlineToBrowser(self::SMS_CONTENT_TYPE, $this->rendering);
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
        $isGoalPluginEnabled = Piwik_Common::isGoalPluginEnabled();
        $prettyDate = $processedReport['prettyDate'];
        $reportData = $processedReport['reportData'];

        $evolutionMetrics = array();
        $multiSitesAPIMetrics = Piwik_MultiSites_API::getApiMetrics($enhanced = true);
        foreach ($multiSitesAPIMetrics as $metricSettings) {
            $evolutionMetrics[] = $metricSettings[Piwik_MultiSites_API::METRIC_EVOLUTION_COL_NAME_KEY];
        }

        // no decimal for all metrics to shorten SMS content (keeps the monetary sign for revenue metrics)
        $reportData->filter(
            'ColumnCallbackReplace',
            array(
                 array_merge(array_keys($multiSitesAPIMetrics), $evolutionMetrics),
                 create_function(
                     '$value',
                     '
                     return preg_replace_callback (
                         "' . self::FLOAT_REGEXP . '",
						create_function (
							\'$matches\',
							\'return round($matches[0]);\'
						),
						$value
					);
					'
                 )
            )
        );

        // evolution metrics formatting :
        //  - remove monetary, percentage and white spaces to shorten SMS content
        //    (this is also needed to be able to test $value != 0 and see if there is an evolution at all in SMSReport.tpl)
        //  - adds a plus sign
        $reportData->filter(
            'ColumnCallbackReplace',
            array(
                 $evolutionMetrics,
                 create_function(
                     '$value',
                     '
                     $matched = preg_match("' . self::FLOAT_REGEXP . '", $value, $matches);
					return $matched ? sprintf("%+d",$matches[0]) : $value;
					'
                 )
            )
        );

        $dataRows = $reportData->getRows();
        $reportMetadata = $processedReport['reportMetadata'];
        $reportRowsMetadata = $reportMetadata->getRows();

        $siteHasECommerce = array();
        foreach ($reportRowsMetadata as $rowMetadata) {
            $idSite = $rowMetadata->getColumn('idsite');
            $siteHasECommerce[$idSite] = Piwik_Site::isEcommerceEnabledFor($idSite);
        }

        $smarty = new Piwik_Smarty();
        $smarty->assign("isGoalPluginEnabled", $isGoalPluginEnabled);
        $smarty->assign("reportRows", $dataRows);
        $smarty->assign("reportRowsMetadata", $reportRowsMetadata);
        $smarty->assign("prettyDate", $prettyDate);
        $smarty->assign("siteHasECommerce", $siteHasECommerce);
        $smarty->assign("displaySiteName", $processedReport['metadata']['action'] == 'getAll');

        // segment
        $segment = $processedReport['segment'];
        $displaySegment = ($segment != null);
        $smarty->assign("displaySegment", $displaySegment);
        if ($displaySegment) {
            $smarty->assign("segmentName", $segment['name']);
        }

        $this->rendering .= $smarty->fetch(PIWIK_USER_PATH . '/plugins/MobileMessaging/templates/SMSReport.tpl');
    }
}

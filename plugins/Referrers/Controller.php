<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable\Map;
use Piwik\Metrics;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        parent::__construct();
    }

    public function index()
    {
        $view = new View('@Referrers/index');

        $view->graphEvolutionReferrers = $this->getEvolutionGraph(Common::REFERRER_TYPE_DIRECT_ENTRY, array(), array('nb_visits'));
        $view->nameGraphEvolutionReferrers = 'Referrers.getEvolutionGraph';

        $nameValues = $this->getReferrersVisitorsByType();

        $totalVisits = array_sum($nameValues);
        foreach ($nameValues as $name => $value) {
            $view->$name = $value;

            // calculate percent of total, if there were any visits
            if ($value != 0
                && $totalVisits != 0
            ) {
                $percentName = $name . 'Percent';
                $view->$percentName = round(($value / $totalVisits) * 100, 0);
            }
        }

        // set distinct metrics
        $distinctMetrics = $this->getDistinctReferrersMetrics();
        foreach ($distinctMetrics as $name => $value) {
            $view->$name = $value;
        }

        // calculate evolution for visit metrics & distinct metrics
        list($lastPeriodDate, $ignore) = Range::getLastDate();
        if ($lastPeriodDate !== false) {
            $date = Common::getRequestVar('date');
            $period = Common::getRequestVar('period');

            $prettyDate = self::getPrettyDate($date, $period);
            $prettyLastPeriodDate = self::getPrettyDate($lastPeriodDate, $period);

            // visit metrics
            $previousValues = $this->getReferrersVisitorsByType($lastPeriodDate);
            $this->addEvolutionPropertiesToView($view, $prettyDate, $nameValues, $prettyLastPeriodDate, $previousValues);

            // distinct metrics
            $previousValues = $this->getDistinctReferrersMetrics($lastPeriodDate);
            $this->addEvolutionPropertiesToView($view, $prettyDate, $distinctMetrics, $prettyLastPeriodDate, $previousValues);
        }

        // sparkline for the historical data of the above values
        $view->urlSparklineSearchEngines = $this->getReferrerUrlSparkline(Common::REFERRER_TYPE_SEARCH_ENGINE);
        $view->urlSparklineDirectEntry = $this->getReferrerUrlSparkline(Common::REFERRER_TYPE_DIRECT_ENTRY);
        $view->urlSparklineWebsites = $this->getReferrerUrlSparkline(Common::REFERRER_TYPE_WEBSITE);
        $view->urlSparklineCampaigns = $this->getReferrerUrlSparkline(Common::REFERRER_TYPE_CAMPAIGN);

        // sparklines for the evolution of the distinct keywords count/websites count/ etc
        $view->urlSparklineDistinctSearchEngines = $this->getUrlSparkline('getLastDistinctSearchEnginesGraph');
        $view->urlSparklineDistinctKeywords = $this->getUrlSparkline('getLastDistinctKeywordsGraph');
        $view->urlSparklineDistinctWebsites = $this->getUrlSparkline('getLastDistinctWebsitesGraph');
        $view->urlSparklineDistinctCampaigns = $this->getUrlSparkline('getLastDistinctCampaignsGraph');

        return $view->render();
    }

    public function allReferrers()
    {
        $view = new View('@Referrers/allReferrers');

        // building the referrers summary report
        $view->dataTableReferrerType = $this->renderReport('getReferrerType');

        $nameValues = $this->getReferrersVisitorsByType();

        $totalVisits = array_sum($nameValues);
        foreach ($nameValues as $name => $value) {
            $view->$name = $value;

            // calculate percent of total, if there were any visits
            if ($value != 0
                && $totalVisits != 0
            ) {
                $percentName = $name . 'Percent';
                $view->$percentName = round(($value / $totalVisits) * 100, 0);
            }
        }

        $view->totalVisits = $totalVisits;
        $view->referrersReportsByDimension = $this->renderReport('getAll');

        return $view->render();
    }

    public function getSearchEnginesAndKeywords()
    {
        $view = new View('@Referrers/getSearchEnginesAndKeywords');
        $view->searchEngines = $this->renderReport('getSearchEngines');
        $view->keywords      = $this->renderReport('getKeywords');
        return $view->render();
    }

    public function indexWebsites()
    {
        $view = new View('@Referrers/indexWebsites');
        $view->websites = $this->renderReport('getWebsites');
        $view->socials  = $this->renderReport('getSocials');

        return $view->render();
    }

    protected function getReferrersVisitorsByType($date = false)
    {
        if ($date === false) {
            $date = Common::getRequestVar('date', false);
        }

        // we disable the queued filters because here we want to get the visits coming from search engines
        // if the filters were applied we would have to look up for a label looking like "Search Engines"
        // which is not good when we have translations
        $dataTableReferrersType = Request::processRequest(
            "Referrers.getReferrerType", array('disable_queued_filters' => '1', 'date' => $date));

        $nameToColumnId = array(
            'visitorsFromSearchEngines' => Common::REFERRER_TYPE_SEARCH_ENGINE,
            'visitorsFromDirectEntry'   => Common::REFERRER_TYPE_DIRECT_ENTRY,
            'visitorsFromWebsites'      => Common::REFERRER_TYPE_WEBSITE,
            'visitorsFromCampaigns'     => Common::REFERRER_TYPE_CAMPAIGN,
        );
        $return = array();
        foreach ($nameToColumnId as $nameVar => $columnId) {
            $value = 0;
            $row = $dataTableReferrersType->getRowFromLabel($columnId);
            if ($row !== false) {
                $value = $row->getColumn(Metrics::INDEX_NB_VISITS);
            }
            $return[$nameVar] = $value;
        }
        return $return;
    }

    protected $referrerTypeToLabel = array(
        Common::REFERRER_TYPE_DIRECT_ENTRY  => 'Referrers_DirectEntry',
        Common::REFERRER_TYPE_SEARCH_ENGINE => 'Referrers_SearchEngines',
        Common::REFERRER_TYPE_WEBSITE       => 'Referrers_Websites',
        Common::REFERRER_TYPE_CAMPAIGN      => 'Referrers_Campaigns',
    );

    public function getEvolutionGraph($typeReferrer = false, array $columns = array(), array $defaultColumns = array())
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'Referrers.getReferrerType');

        $view->config->add_total_row = true;

        // configure displayed columns
        if (empty($columns)) {
            $columns = Common::getRequestVar('columns', false);
            if (false !== $columns) {
                $columns = Piwik::getArrayFromApiParameter($columns);
            }
        }
        if (false !== $columns) {
            $columns = !is_array($columns) ? array($columns) : $columns;
        }

        if (!empty($columns)) {
            $view->config->columns_to_display = $columns;
        } elseif (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
            $view->config->columns_to_display = $defaultColumns;
        }

        // configure selectable columns
        // todo: should use SettingsPiwik::isUniqueVisitorsEnabled
        if (Common::getRequestVar('period', false) == 'day') {
            $selectable = array('nb_visits', 'nb_uniq_visitors', 'nb_users', 'nb_actions');
        } else {
            $selectable = array('nb_visits', 'nb_actions');
        }
        $view->config->selectable_columns = $selectable;

        // configure displayed rows
        $visibleRows = Common::getRequestVar('rows', false);
        if ($visibleRows !== false) {
            // this happens when the row picker has been used
            $visibleRows = Piwik::getArrayFromApiParameter($visibleRows);

            // typeReferrer is redundant if rows are defined, so make sure it's not used
            $view->config->custom_parameters['typeReferrer'] = false;
        } else {
            // use $typeReferrer as default
            if ($typeReferrer === false) {
                $typeReferrer = Common::getRequestVar('typeReferrer', false);
            }
            $label = self::getTranslatedReferrerTypeLabel($typeReferrer);
            $total = $this->translator->translate('General_Total');

            if (!empty($view->config->rows_to_display)) {
                $visibleRows = $view->config->rows_to_display;
            } else {
                $visibleRows = array($label, $total);
            }

            $view->requestConfig->request_parameters_to_modify['rows'] = $label . ',' . $total;
        }
        $view->config->row_picker_match_rows_by = 'label';
        $view->config->rows_to_display = $visibleRows;

        $view->config->documentation = $this->translator->translate('Referrers_EvolutionDocumentation') . '<br />'
            . $this->translator->translate('General_BrokenDownReportDocumentation') . '<br />'
            . $this->translator->translate('Referrers_EvolutionDocumentationMoreInfo', '&quot;'
                . $this->translator->translate('Referrers_ReferrerTypes') . '&quot;');

        return $this->renderView($view);
    }

    public function getLastDistinctSearchEnginesGraph()
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referrers.getNumberOfDistinctSearchEngines");
        $view->config->translations['Referrers_distinctSearchEngines'] = ucfirst($this->translator->translate('Referrers_DistinctSearchEngines'));
        $view->config->columns_to_display = array('Referrers_distinctSearchEngines');
        return $this->renderView($view);
    }

    public function getLastDistinctKeywordsGraph()
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referrers.getNumberOfDistinctKeywords");
        $view->config->translations['Referrers_distinctKeywords'] = ucfirst($this->translator->translate('Referrers_DistinctKeywords'));
        $view->config->columns_to_display = array('Referrers_distinctKeywords');
        return $this->renderView($view);
    }

    public function getLastDistinctWebsitesGraph()
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referrers.getNumberOfDistinctWebsites");
        $view->config->translations['Referrers_distinctWebsites'] = ucfirst($this->translator->translate('Referrers_DistinctWebsites'));
        $view->config->columns_to_display = array('Referrers_distinctWebsites');
        return $this->renderView($view);
    }

    public function getLastDistinctCampaignsGraph()
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referrers.getNumberOfDistinctCampaigns");
        $view->config->translations['Referrers_distinctCampaigns'] = ucfirst($this->translator->translate('Referrers_DistinctCampaigns'));
        $view->config->columns_to_display = array('Referrers_distinctCampaigns');
        return $this->renderView($view);
    }

    function getKeywordsForPage()
    {
        Piwik::checkUserHasViewAccess($this->idSite);

        $requestUrl = '&date=previous1'
            . '&period=week'
            . '&idSite=' . $this->idSite;

        $topPageUrlRequest = $requestUrl
            . '&method=Actions.getPageUrls'
            . '&filter_limit=50'
            . '&format=original';
        $request = new Request($topPageUrlRequest);
        $request = $request->process();
        /** @var $request Map */
        $tables = $request->getDataTables();;

        $topPageUrl = false;
        $first = key($tables);
        if (!empty($first)) {
            $topPageUrls = $tables[$first];
            $topPageUrls = $topPageUrls->getRowsMetadata('url');
            $tmpTopPageUrls = array_values($topPageUrls);
            $topPageUrl = current($tmpTopPageUrls);
        }
        if (empty($topPageUrl)) {
            $topPageUrl = $this->site->getMainUrl();
        }
        $url = $topPageUrl;

        // HTML
        $api = SettingsPiwik::getPiwikUrl()
            . '?module=API&method=Referrers.getKeywordsForPageUrl'
            . '&format=json'
            . '&filter_limit=10'
            . '&token_auth=' . Piwik::getCurrentUserTokenAuth();

        $api .= $requestUrl;
        $code = '
// This function will call the API to get best keyword for current URL.
// Then it writes the list of best keywords in a HTML list
function DisplayTopKeywords($url = "")
{
	// Do not spend more than 1 second fetching the data
	@ini_set("default_socket_timeout", $timeout = 1);
	// Get the Keywords data
	$url = empty($url) ? "http://". $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] : $url;
	$api = "' . $api . '&url=" . urlencode($url);
	$keywords = @json_decode(file_get_contents($api), $assoc = true);
	Common::sendHeader(\'Content-Type: text/html; charset=utf-8\', true);
	if ($keywords === false || isset($keywords["result"])) {
		// DEBUG ONLY: uncomment for troubleshooting an empty output (the URL output reveals the token_auth)
		// echo "Error while fetching the <a href=\'$api\'>Top Keywords from Piwik</a>";
		return;
	}

	// Display the list in HTML
	$url = htmlspecialchars($url, ENT_QUOTES);
	$output = "<h2>Top Keywords for <a href=\'$url\'>$url</a></h2><ul>";
	foreach($keywords as $keyword) {
		$output .= "<li>". $keyword . "</li>";
	}
	if (empty($keywords)) { $output .= "Nothing yet..."; }
	$output .= "</ul>";
	echo $output;
}
';

        $jsonRequest = str_replace('format=php', 'format=json', $api);
        echo "<p>This widget is designed to work in your website directly.
		This widget makes it easy to use Piwik to <i>automatically display the list of Top Keywords</i>, for each of your website Page URLs.</p>
		<p>
		<b>Example API URL</b> - For example if you would like to get the top 10 keywords, used last week, to land on the page <a rel='noreferrer' target='_blank' href='$topPageUrl'>$topPageUrl</a>,
		in format JSON: you would dynamically fetch the data using <a rel='noreferrer' target='_blank' href='$jsonRequest&url=" . urlencode($topPageUrl) . "'>this API request URL</a>. Make sure you encode the 'url' parameter in the URL.</p>

		<p><b>PHP Function ready to use!</b> - If you use PHP on your website, we have prepared a small code snippet that you can copy paste in your Website PHP files. You can then simply call the function <code>DisplayTopKeywords();</code> anywhere in your template, at the bottom of the content or in your blog sidebar.
		If you run this code in your page $topPageUrl, it would output the following:";

        echo "<div style='width:400px;margin-left:20px;padding:10px;border:1px solid black;'>";
        function DisplayTopKeywords($url = "", $api)
        {
            // Do not spend more than 1 second fetching the data
            @ini_set("default_socket_timeout", $timeout = 1);
            // Get the Keywords data
            $url = empty($url) ? "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] : $url;
            $api = $api . "&url=" . urlencode($url);
            $keywords = @json_decode(file_get_contents($api), $assoc = true);
            Common::sendHeader('Content-Type: text/html; charset=utf-8', true);
            if ($keywords === false || isset($keywords["result"])) {
                // DEBUG ONLY: uncomment for troubleshooting an empty output (the URL output reveals the token_auth)
                //echo "Error while fetching the <a href=\'".$api."\'>Top Keywords from Piwik</a>";
                return;
            }

            // Display the list in HTML
            $url = htmlspecialchars($url, ENT_QUOTES);
            $output = "<h2>Top Keywords for <a href=\'$url\'>$url</a></h2><ul>";
            foreach ($keywords as $keyword) {
                $output .= "<li>" . $keyword . "</li>";
            }
            if (empty($keywords)) {
                $output .= "Nothing yet...";
            }
            $output .= "</ul>";
            echo $output;
        }

        DisplayTopKeywords($topPageUrl, $api);

        echo "</div><br/>
		<p>Here is the PHP function that you can paste in your pages:</P>
		<textarea cols=60 rows=8>&lt;?php\n" . htmlspecialchars($code) . "\n DisplayTopKeywords();</textarea>
		";

        echo "
		<p><strong>Notes</strong>: You can for example edit the code to to make the Top search keywords link to your Website search result pages.
		<br/>On medium to large traffic websites, we recommend to cache this data, as to minimize the performance impact of calling the Piwik API on each page view.
		</p>
		";
    }

    /**
     * Returns the i18n-ized label for a referrer type.
     *
     * @param int $typeReferrer The referrer type. Referrer types are defined in Common class.
     * @return string The i18n-ized label.
     */
    public static function getTranslatedReferrerTypeLabel($typeReferrer)
    {
        $label = getReferrerTypeLabel($typeReferrer);
        return Piwik::translate($label);
    }

    /**
     * Returns the URL for the sparkline of visits with a specific referrer type.
     *
     * @param int $referrerType The referrer type. Referrer types are defined in Common class.
     * @return string The URL that can be used to get a sparkline image.
     */
    private function getReferrerUrlSparkline($referrerType)
    {
        $totalRow = $this->translator->translate('General_Total');
        return $this->getUrlSparkline(
            'getEvolutionGraph',
            array('columns'      => array('nb_visits'),
                  'rows'         => array(self::getTranslatedReferrerTypeLabel($referrerType), $totalRow),
                  'typeReferrer' => $referrerType)
        );
    }

    /**
     * Returns an array containing the number of distinct referrers for each
     * referrer type.
     *
     * @param bool|string $date The date to use when getting metrics. If false, the
     *                           date query param is used.
     * @return array The metrics.
     */
    private function getDistinctReferrersMetrics($date = false)
    {
        $propertyToAccessorMapping = array(
            'numberDistinctSearchEngines' => 'getNumberOfDistinctSearchEngines',
            'numberDistinctKeywords'      => 'getNumberOfDistinctKeywords',
            'numberDistinctWebsites'      => 'getNumberOfDistinctWebsites',
            'numberDistinctWebsitesUrls'  => 'getNumberOfDistinctWebsitesUrls',
            'numberDistinctCampaigns'     => 'getNumberOfDistinctCampaigns',
        );

        $result = array();
        foreach ($propertyToAccessorMapping as $property => $method) {
            $result[$property] = $this->getNumericValue('Referrers.' . $method, $date);
        }
        return $result;
    }

    /**
     * Utility method that calculates evolution values for a set of current & past values
     * and sets properties on a View w/ HTML that displays the evolution percents.
     *
     * @param View $view The view to set properties on.
     * @param string $date The date of the current values.
     * @param array $currentValues Array mapping view property names w/ present values.
     * @param string $lastPeriodDate The date of the period in the past.
     * @param array $previousValues Array mapping view property names w/ past values. Keys
     *                              in this array should be the same as keys in $currentValues.
     */
    private function addEvolutionPropertiesToView($view, $date, $currentValues, $lastPeriodDate, $previousValues)
    {
        foreach ($previousValues as $name => $pastValue) {
            $currentValue = $currentValues[$name];
            $evolutionName = $name . 'Evolution';

            $view->$evolutionName = $this->getEvolutionHtml($date, $currentValue, $lastPeriodDate, $pastValue);
        }
    }
}

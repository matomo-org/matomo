<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Piwik\API\DocumentationGenerator;
use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugins\API\Renderer\Original;
use Piwik\Url;
use Piwik\UrlHelper;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    function index()
    {
        $tokenAuth = Common::getRequestVar('token_auth', 'anonymous', 'string');
        $format = Common::getRequestVar('format', false);
        $serialize = Common::getRequestVar('serialize', false);

        $token = 'token_auth=' . $tokenAuth;

        // when calling the API through http, we limit the number of returned results
        if (!isset($_GET['filter_limit'])) {
            if (isset($_POST['filter_limit'])) {
                $_GET['filter_limit'] = $_POST['filter_limit'];
            } else {
                $_GET['filter_limit'] = Config::getInstance()->General['API_datatable_default_limit'];
            }
        }

        $request  = new Request($token);
        $response = $request->process();

        if (is_array($response)) {
            if ($format == 'original'
                && $serialize != 1
            ) {
                Original::sendPlainTextHeader();
            }

            $response = var_export($response, true);
        }

        return $response;
    }

    public function listAllMethods()
    {
        Piwik::checkUserHasSomeViewAccess();

        $ApiDocumentation = new DocumentationGenerator();
        $prefixUrls = Common::getRequestVar('prefixUrl', 'https://demo.matomo.org/', 'string');
        $hostname = parse_url($prefixUrls, PHP_URL_HOST);
        if (empty($hostname) || !UrlHelper::isLookLikeUrl($prefixUrls) || strpos($prefixUrls, 'http') !== 0 || !Url::isValidHost($hostname)) {
            $prefixUrls = '';
        }
        return $ApiDocumentation->getApiDocumentationAsStringForDeveloperReference($outputExampleUrls = true, $prefixUrls);
    }

    public function listAllAPI()
    {
        $view = new View("@API/listAllAPI");
        $this->setGeneralVariablesView($view);

        $ApiDocumentation = new DocumentationGenerator();
        $view->countLoadedAPI = Proxy::getInstance()->getCountRegisteredClasses();
        $view->list_api_methods_with_links = str_replace('href=\'#', 'href=\'#/', $ApiDocumentation->getApiDocumentationAsString());
        return $view->render();
    }

    public function listSegments()
    {
        $segments = API::getInstance()->getSegmentsMetadata($this->idSite);

        $tableDimensions = $tableMetrics = '';
        $customVariables = 0;
        $lastCategory = array();
        foreach ($segments as $segment) {
            $onlyDisplay = array('customVariableName1', 'customVariableName2',
                                 'customVariableValue1', 'customVariableValue2',
                                 'customVariablePageName1', 'customVariablePageValue1');

            $customVariableWillBeDisplayed = in_array($segment['segment'], $onlyDisplay);
            // Don't display more than 4 custom variables name/value rows
            if ($segment['category'] == 'Custom Variables'
                && !$customVariableWillBeDisplayed
            ) {
                continue;
            }

            $thisCategory = $segment['category'];
            $output = '';
            if (empty($lastCategory[$segment['type']])
                || $lastCategory[$segment['type']] != $thisCategory
            ) {
                $output .= '<tr><td class="segmentCategory" colspan="2"><b>' . $thisCategory . '</b></td></tr>';
            }

            $lastCategory[$segment['type']] = $thisCategory;

            $exampleValues = isset($segment['acceptedValues'])
                ? 'Example values: <code>' . $segment['acceptedValues'] . '</code>'
                : '';
            $restrictedToAdmin = isset($segment['permission']) ? '<br/>Note: This segment can only be used by an Admin user' : '';
            $output .= '<tr>
							<td class="segmentString">' . $segment['segment'] . '</td>
							<td class="segmentName">' . $segment['name'] . $restrictedToAdmin . '<br/>' . $exampleValues . ' </td>
						</tr>';

            // Show only 2 custom variables and display message for rest
            if ($customVariableWillBeDisplayed) {
                $customVariables++;
                if ($customVariables == count($onlyDisplay)) {
                    $output .= '<tr><td colspan="2"> There are 5 custom variables available, so you can segment across any segment name and value range.
    						<br/>For example, <code>customVariableName1==Type;customVariableValue1==Customer</code>
    						<br/>Returns all visitors that have the Custom Variable "Type" set to "Customer".
    						<br/>Custom Variables of scope "page" can be queried separately. For example, to query the Custom Variable of scope "page",
    						<br/>stored in index 1, you would use the segment <code>customVariablePageName1==ArticleLanguage;customVariablePageValue1==FR</code>
    						</td></tr>';
                }
            }

            if ($segment['type'] == 'dimension') {
                $tableDimensions .= $output;
            } else {
                $tableMetrics .= $output;
            }
        }

        return "
		<strong>Dimensions</strong>
		<table>
		$tableDimensions
		</table>
		<br/>
		<strong>Metrics</strong>
		<table>
		$tableMetrics
		</table>
		";
    }

    public function glossary()
    {
        Piwik::checkUserHasSomeViewAccess();

        $reports = Request::processRequest('API', array('method' => 'API.getGlossaryReports', 'filter_limit' => -1));
        $metrics = Request::processRequest('API', array('method' => 'API.getGlossaryMetrics', 'filter_limit' => -1));

        $glossaryItems = array(
            'metrics' => array(
                'title' => Piwik::translate('General_Metrics'),
                'entries' => $metrics
            ),
            'reports' => array(
                'title' => Piwik::translate('General_Reports'),
                'entries' => $reports
            )
        );

        /**
         * Triggered to add or modify glossary items. You can either modify one of the existing core categories
         * 'reports' and 'metrics' or add your own category.
         *
         * **Example**
         *
         *     public function addGlossaryItems(&$glossaryItems)
         *     {
         *          $glossaryItems['users'] = array('title' => 'Users', 'entries' => array(
         *              array('name' => 'User1', 'documentation' => 'This user has ...'),
         *              array('name' => 'User2', 'documentation' => 'This user has ...'),
         *          ));
         *          $glossaryItems['reports']['entries'][] = array('name' => 'My Report', 'documentation' => 'This report has ...');
         *     }
         *
         * @param array &$glossaryItems An array containing all glossary items.
         */
        Piwik::postEvent('API.addGlossaryItems', array(&$glossaryItems));

        foreach ($glossaryItems as &$item) {
            $item['letters'] = array();
            foreach ($item['entries'] as &$entry) {
                $cleanEntryName = mb_ereg_replace('["\']', '', $entry['name']);
                $letter = mb_strtoupper(mb_substr($cleanEntryName, 0, 1));
                $entry['letter'] = $letter;
                $item['letters'][$letter] = $letter;
            }
        }

        return $this->renderTemplate('glossary', array(
            'glossaryItems' => $glossaryItems,
        ));
    }
}

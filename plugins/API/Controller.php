<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
use Piwik\Plugin\Report;
use Piwik\Url;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    function index()
    {
        $token = 'token_auth=' . Common::getRequestVar('token_auth', 'anonymous', 'string');

        // when calling the API through http, we limit the number of returned results
        if (!isset($_GET['filter_limit'])) {
            $_GET['filter_limit'] = Config::getInstance()->General['API_datatable_default_limit'];
            $token .= '&api_datatable_default_limit=' . $_GET['filter_limit'];
        }

        $request  = new Request($token);
        $response = $request->process();

        if (is_array($response)) {
            $response = var_export($response, true);
        }

        return $response;
    }

    public function listAllMethods()
    {
        $ApiDocumentation = new DocumentationGenerator();
        return $ApiDocumentation->getAllInterfaceString($outputExampleUrls = true, $prefixUrls = Common::getRequestVar('prefixUrl', ''));
    }

    public function listAllAPI()
    {
        $view = new View("@API/listAllAPI");
        $this->setGeneralVariablesView($view);

        $ApiDocumentation = new DocumentationGenerator();
        $view->countLoadedAPI = Proxy::getInstance()->getCountRegisteredClasses();
        $view->list_api_methods_with_links = $ApiDocumentation->getAllInterfaceString();
        return $view->render();
    }

    public function listSegments()
    {
        $segments = API::getInstance()->getSegmentsMetadata($this->idSite);

        $tableDimensions = $tableMetrics = '';
        $customVariables = 0;
        $lastCategory = array();
        foreach ($segments as $segment) {
            // Eg. Event Value is a metric, not in the Visit metric category,
            // we make sure it is displayed along with the Events dimensions
            if ($segment['type'] == 'metric' && $segment['category'] != Piwik::translate('General_Visit')) {
                $segment['type'] = 'dimension';
            }

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

        return $this->renderTemplate('glossary', array(
            'reports' => Request::processRequest('API', array('method' => 'API.getGlossaryReports')),
            'metrics' => Request::processRequest('API', array('method' => 'API.getGlossaryMetrics')),
        ));
    }
}

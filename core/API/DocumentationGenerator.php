<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\API;

use Exception;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Url;

class DocumentationGenerator
{
    protected $modulesToHide = array('CoreAdminHome', 'DBStats');
    protected $countPluginsLoaded = 0;

    /**
     * trigger loading all plugins with an API.php file in the Proxy
     */
    public function __construct()
    {
        $plugins = \Piwik\Plugin\Manager::getInstance()->getLoadedPluginsName();
        foreach ($plugins as $plugin) {
            try {
                $className = Request::getClassNameAPI($plugin);
                Proxy::getInstance()->registerClass($className);
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Returns a HTML page containing help for all the successfully loaded APIs.
     *  For each module it will return a mini help with the method names, parameters to give,
     * links to get the result in Xml/Csv/etc
     *
     * @param bool $outputExampleUrls
     * @param string $prefixUrls
     * @return string
     */
    public function getAllInterfaceString($outputExampleUrls = true, $prefixUrls = '')
    {
        if (!empty($prefixUrls)) {
            $prefixUrls = 'http://demo.piwik.org/';
        }
        $str = $toc = '';
        $token_auth = "&token_auth=" . Piwik::getCurrentUserTokenAuth();
        $parametersToSet = array(
            'idSite' => Common::getRequestVar('idSite', 1, 'int'),
            'period' => Common::getRequestVar('period', 'day', 'string'),
            'date'   => Common::getRequestVar('date', 'today', 'string')
        );

        foreach (Proxy::getInstance()->getMetadata() as $class => $info) {
            $moduleName = Proxy::getInstance()->getModuleNameFromClassName($class);
            if (in_array($moduleName, $this->modulesToHide)) {
                continue;
            }
            $toc .= "<a href='#$moduleName'>$moduleName</a><br/>";
            $str .= "\n<a  name='$moduleName' id='$moduleName'></a><h2>Module " . $moduleName . "</h2>";
            $str .= "<div class='apiDescription'> " . $info['__documentation'] . " </div>";
            foreach ($info as $methodName => $infoMethod) {
                if ($methodName == '__documentation') {
                    continue;
                }
                $params = $this->getParametersString($class, $methodName);
                $str .= "\n <div class='apiMethod'>- <b>$moduleName.$methodName </b>" . $params . "";
                $str .= '<small>';

                if ($outputExampleUrls) {
                    // we prefix all URLs with $prefixUrls
                    // used when we include this output in the Piwik official documentation for example
                    $str .= "<span class=\"example\">";
                    $exampleUrl = $this->getExampleUrl($class, $methodName, $parametersToSet);
                    if ($exampleUrl !== false) {
                        $lastNUrls = '';
                        if (preg_match('/(&period)|(&date)/', $exampleUrl)) {
                            $exampleUrlRss = $prefixUrls . $this->getExampleUrl($class, $methodName, array('date' => 'last10', 'period' => 'day') + $parametersToSet);
                            $lastNUrls = ",	RSS of the last <a target=_blank href='$exampleUrlRss&format=rss$token_auth&translateColumnNames=1'>10 days</a>";
                        }
                        $exampleUrl = $prefixUrls . $exampleUrl;
                        $str .= " [ Example in
									<a target=_blank href='$exampleUrl&format=xml$token_auth'>XML</a>,
									<a target=_blank href='$exampleUrl&format=JSON$token_auth'>Json</a>,
									<a target=_blank href='$exampleUrl&format=Tsv$token_auth&translateColumnNames=1'>Tsv (Excel)</a>
									$lastNUrls
									]";
                    } else {
                        $str .= " [ No example available ]";
                    }
                    $str .= "</span>";
                }
                $str .= '</small>';
                $str .= "</div>\n";
            }
            $str .= '<div style="margin:15px;"><a href="#topApiRef">↑ Back to top</a></div>';
        }

        $str = "<h2 id='topApiRef' name='topApiRef'>Quick access to APIs</h2>
				$toc
				$str";
        return $str;
    }

    /**
     * Returns a string containing links to examples on how to call a given method on a given API
     * It will export links to XML, CSV, HTML, JSON, PHP, etc.
     * It will not export links for methods such as deleteSite or deleteUser
     *
     * @param string $class the class
     * @param string $methodName the method
     * @param array $parametersToSet parameters to set
     * @return string|bool when not possible
     */
    public function getExampleUrl($class, $methodName, $parametersToSet = array())
    {
        $knowExampleDefaultParametersValues = array(
            'access'         => 'view',
            'userLogin'      => 'test',
            'passwordMd5ied' => 'passwordExample',
            'email'          => 'test@example.org',

            'languageCode'   => 'fr',
            'url'            => 'http://forum.piwik.org/',
            'pageUrl'        => 'http://forum.piwik.org/',
            'apiModule'      => 'UserCountry',
            'apiAction'      => 'getCountry',
            'lastMinutes'    => '30',
            'abandonedCarts' => '0',
            'segmentName'    => 'pageTitle',
            'ip'             => '194.57.91.215',
            'idSites'             => '1,2',
            'idAlert'             => '1',
            'seconds'        => '3600',
//            'segmentName'    => 'browserCode',
        );

        foreach ($parametersToSet as $name => $value) {
            $knowExampleDefaultParametersValues[$name] = $value;
        }

        // no links for these method names
        $doNotPrintExampleForTheseMethods = array(
            //Sites
            'deleteSite',
            'addSite',
            'updateSite',
            'addSiteAliasUrls',
            //Users
            'deleteUser',
            'addUser',
            'updateUser',
            'setUserAccess',
            //Goals
            'addGoal',
            'updateGoal',
            'deleteGoal',
        );

        if (in_array($methodName, $doNotPrintExampleForTheseMethods)) {
            return false;
        }

        // we try to give an URL example to call the API
        $aParameters = Proxy::getInstance()->getParametersList($class, $methodName);
        // Kindly force some known generic parameters to appear in the final list
        // the parameter 'format' can be set to all API methods (used in tests)
        // the parameter 'hideIdSubDatable' is used for integration tests only
        // the parameter 'serialize' sets php outputs human readable, used in integration tests and debug
        // the parameter 'language' sets the language for the response (eg. country names)
        // the parameter 'flat' reduces a hierarchical table to a single level by concatenating labels
        // the parameter 'include_aggregate_rows' can be set to include inner nodes in flat reports
        // the parameter 'translateColumnNames' can be set to translate metric names in csv/tsv exports
        $aParameters['format'] = false;
        $aParameters['hideIdSubDatable'] = false;
        $aParameters['serialize'] = false;
        $aParameters['language'] = false;
        $aParameters['translateColumnNames'] = false;
        $aParameters['label'] = false;
        $aParameters['flat'] = false;
        $aParameters['include_aggregate_rows'] = false;
        $aParameters['filter_limit'] = false; //@review without adding this, I can not set filter_limit in $otherRequestParameters integration tests
        $aParameters['filter_sort_column'] = false; //@review without adding this, I can not set filter_sort_column in $otherRequestParameters integration tests
        $aParameters['filter_sort_order'] = false; //@review without adding this, I can not set filter_sort_order in $otherRequestParameters integration tests
        $aParameters['filter_truncate'] = false;
        $aParameters['hideColumns'] = false;
        $aParameters['showColumns'] = false;
        $aParameters['filter_pattern_recursive'] = false;
        $aParameters['pivotBy'] = false;
        $aParameters['pivotByColumn'] = false;
        $aParameters['pivotByColumnLimit'] = false;
        $aParameters['disable_queued_filters'] = false;
        $aParameters['disable_generic_filters'] = false;

        $moduleName = Proxy::getInstance()->getModuleNameFromClassName($class);
        $aParameters = array_merge(array('module' => 'API', 'method' => $moduleName . '.' . $methodName), $aParameters);

        foreach ($aParameters as $nameVariable => &$defaultValue) {
            if (isset($knowExampleDefaultParametersValues[$nameVariable])) {
                $defaultValue = $knowExampleDefaultParametersValues[$nameVariable];
            } // if there isn't a default value for a given parameter,
            // we need a 'know default value' or we can't generate the link
            elseif ($defaultValue instanceof NoDefaultValue) {
                return false;
            }
        }
        return '?' . Url::getQueryStringFromParameters($aParameters);
    }

    /**
     * Returns the methods $class.$name parameters (and default value if provided) as a string.
     *
     * @param string $class The class name
     * @param string $name The method name
     * @return string  For example "(idSite, period, date = 'today')"
     */
    public function getParametersString($class, $name)
    {
        $aParameters = Proxy::getInstance()->getParametersList($class, $name);
        $asParameters = array();
        foreach ($aParameters as $nameVariable => $defaultValue) {
            // Do not show API parameters starting with _
            // They are supposed to be used only in internal API calls
            if (strpos($nameVariable, '_') === 0) {
                continue;
            }
            $str = $nameVariable;
            if (!($defaultValue instanceof NoDefaultValue)) {
                if (is_array($defaultValue)) {
                    $str .= " = 'Array'";
                } else {
                    $str .= " = '$defaultValue'";
                }
            }
            $asParameters[] = $str;
        }
        $sParameters = implode(", ", $asParameters);
        return "($sParameters)";
    }
}

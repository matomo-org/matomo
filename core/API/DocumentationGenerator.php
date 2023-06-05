<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\API;

use Exception;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Url;
use ReflectionClass;

/**
 * Possible tags to use in APIs
 *
 * @hide -> Won't be shown in list of all APIs but is also not possible to be called via HTTP API
 * @hideForAll Same as @hide
 * @hideExceptForSuperUser Same as @hide but still shown and possible to be called by a user with super user access
 * @internal -> Won't be shown in list of all APIs but is possible to be called via HTTP API
 */
class DocumentationGenerator
{
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
     *
     * @param bool $outputExampleUrls
     * @return string
     */
    public function getApiDocumentationAsString($outputExampleUrls = true)
    {
        list($toc, $str) = $this->generateDocumentation($outputExampleUrls, $prefixUrls = '', $displayTitlesAsAngularDirective = true);

        return "<div piwik-content-block content-title='Quick access to APIs' id='topApiRef' name='topApiRef'>
				$toc</div>
				$str";
    }

    /**
     * Used on developer.piwik.org
     *
     * @param bool|true $outputExampleUrls
     * @param string $prefixUrls
     * @return string
     */
    public function getApiDocumentationAsStringForDeveloperReference($outputExampleUrls = true, $prefixUrls = '')
    {
        list($toc, $str) = $this->generateDocumentation($outputExampleUrls, $prefixUrls, $displayTitlesAsAngularDirective = false);

        return "<h2 id='topApiRef' name='topApiRef'>Quick access to APIs</h2>
				$toc
				$str";
    }

    protected function prepareModuleToDisplay($moduleName)
    {
        return "<a href='#$moduleName'>$moduleName</a><br/>";
    }

    protected function prepareMethodToDisplay($moduleName, $info, $methods, $class, $outputExampleUrls, $prefixUrls, $displayTitlesAsAngularDirective)
    {
        $str = '';
        $str .= "\n<a name='$moduleName' id='$moduleName'></a>";
        if($displayTitlesAsAngularDirective) {
            $str .= "<div piwik-content-block content-title='Module " . $moduleName . "'>";
        } else {
            $str .= "<h2>Module " . $moduleName . "</h2>";
        }
        $info['__documentation'] = $this->checkDocumentation($info['__documentation']);
        $str .= "<div class='apiDescription'> " . $info['__documentation'] . " </div>";
        foreach ($methods as $methodName) {
            if (Proxy::getInstance()->isDeprecatedMethod($class, $methodName)) {
                continue;
            }

            $params = $this->getParametersString($class, $methodName);

            $str .= "\n <div class='apiMethod'>- <b>$moduleName.$methodName </b>" . $params . "";
            $str .= '<small>';
            if ($outputExampleUrls) {
                $str .= $this->addExamples($class, $methodName, $prefixUrls);
            }
            $str .= '</small>';
            $str .= "</div>\n";
        }

        if($displayTitlesAsAngularDirective) {
            $str .= "</div>";
        }

        return $str;
    }

    protected function prepareModulesAndMethods($info, $moduleName)
    {
        $toDisplay = array();

        foreach ($info as $methodName => $infoMethod) {
            if ($methodName == '__documentation') {
                continue;
            }
            $toDisplay[$moduleName][] = $methodName;
        }

        return $toDisplay;
    }

    protected function addExamples($class, $methodName, $prefixUrls)
    {
        $token = Piwik::getCurrentUserTokenAuth();
        $token_auth_url = "&token_auth=" . $token;
        if ($token !== 'anonymous') {
            $token_auth_url .= "&force_api_session=1";
        }
        $parametersToSet = array(
            'idSite' => Common::getRequestVar('idSite', 1, 'int'),
            'period' => Common::getRequestVar('period', 'day', 'string'),
            'date' => Common::getRequestVar('date', 'today', 'string')
        );
        $str = '';
        $str .= "<span class=\"example\">";
        $exampleUrl = $this->getExampleUrl($class, $methodName, $parametersToSet);
        if ($exampleUrl !== false) {
            $lastNUrls = '';
            if (preg_match('/(&period)|(&date)/', $exampleUrl)) {
                $exampleUrlRss = $prefixUrls . $this->getExampleUrl($class, $methodName, array('date' => 'last10', 'period' => 'day') + $parametersToSet);
                $lastNUrls = ", RSS of the last <a target='_blank' href='$exampleUrlRss&format=rss$token_auth_url&translateColumnNames=1'>10 days</a>";
            }
            $exampleUrl = $prefixUrls . $exampleUrl;
            $str .= " [ Example in
                                                                    <a target='_blank' href='$exampleUrl&format=xml$token_auth_url'>XML</a>,
                                                                    <a target='_blank' href='$exampleUrl&format=JSON$token_auth_url'>Json</a>,
                                                                    <a target='_blank' href='$exampleUrl&format=Tsv$token_auth_url&translateColumnNames=1'>Tsv (Excel)</a>
                                                                    $lastNUrls
                                                                    ]";
        } else {
            $str .= " [ No example available ]";
        }
        $str .= "</span>";
        return $str;
    }

    /**
     * Check if Class contains @hide
     *
     * @param ReflectionClass $rClass instance of ReflectionMethod
     * @return bool
     */
    public function checkIfClassCommentContainsHideAnnotation(ReflectionClass $rClass)
    {
        return false !== strstr($rClass->getDocComment(), '@hide');
    }

    /**
     * Check if Class contains @internal
     *
     * @param ReflectionClass|\ReflectionMethod $rClass instance of ReflectionMethod
     * @return bool
     */
    private function checkIfCommentContainsInternalAnnotation($rClass)
    {
        return false !== strstr($rClass->getDocComment(), '@internal');
    }

    /**
     * Check if documentation contains @hide annotation and deletes it
     *
     * @param $moduleToCheck
     * @return mixed
     */
    public function checkDocumentation($moduleToCheck)
    {
        if (strpos($moduleToCheck, '@hide') == true) {
            $moduleToCheck = str_replace(strtok(strstr($moduleToCheck, '@hide'), "\n"), "", $moduleToCheck);
        }
        return $moduleToCheck;
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
            'url'            => 'https://divezone.net/',
            'pageUrl'        => 'https://divezone.net/',
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
            //Marketplace
            'deleteLicenseKey'
        );

        if (in_array($methodName, $doNotPrintExampleForTheseMethods)) {
            return false;
        }

        // we try to give an URL example to call the API
        $aParameters = Proxy::getInstance()->getParametersList($class, $methodName);
        $aParameters['format'] = false;
        $aParameters['hideIdSubDatable'] = false;
        $aParameters['serialize'] = false;
        $aParameters['language'] = false;
        $aParameters['translateColumnNames'] = false;
        $aParameters['label'] = false;
        $aParameters['labelSeries'] = false;
        $aParameters['flat'] = false;
        $aParameters['include_aggregate_rows'] = false;
        $aParameters['filter_offset'] = false; 
        $aParameters['filter_limit'] = false; 
        $aParameters['filter_sort_column'] = false; 
        $aParameters['filter_sort_order'] = false; 
        $aParameters['filter_excludelowpop'] = false; 
        $aParameters['filter_excludelowpop_value'] = false; 
        $aParameters['filter_column_recursive'] = false; 
        $aParameters['filter_pattern'] = false; 
        $aParameters['filter_pattern_recursive'] = false; 
        $aParameters['filter_truncate'] = false;
        $aParameters['hideColumns'] = false;
        $aParameters['hideColumnsRecursively'] = false;
        $aParameters['showColumns'] = false;
        $aParameters['pivotBy'] = false;
        $aParameters['pivotByColumn'] = false;
        $aParameters['pivotByColumnLimit'] = false;
        $aParameters['disable_queued_filters'] = false;
        $aParameters['disable_generic_filters'] = false;
        $aParameters['expanded'] = false;
        $aParameters['idDimenson'] = false;
        $aParameters['format_metrics'] = false;
        $aParameters['compare'] = false;
        $aParameters['compareDates'] = false;
        $aParameters['comparePeriods'] = false;
        $aParameters['compareSegments'] = false;
        $aParameters['comparisonIdSubtables'] = false;
        $aParameters['invert_compare_change_compute'] = false;
        $aParameters['filter_update_columns_when_show_all_goals'] = false;
        $aParameters['filter_show_goal_columns_process_goals'] = false;

        $extraParameters = StaticContainer::get('entities.idNames');
        $extraParameters = array_merge($extraParameters, StaticContainer::get('DocumentationGenerator.customParameters'));
        foreach ($extraParameters as $paramName) {
            if (isset($aParameters[$paramName])) {
                continue;
            }
            $aParameters[$paramName] = false;
        }

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
    protected function getParametersString($class, $name)
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

    /**
     * @param $outputExampleUrls
     * @param $prefixUrls
     * @param $displayTitlesAsAngularDirective
     * @return array
     */
    protected function generateDocumentation($outputExampleUrls, $prefixUrls, $displayTitlesAsAngularDirective)
    {
        $str = $toc = '';

        foreach (Proxy::getInstance()->getMetadata() as $class => $info) {
            $moduleName = Proxy::getInstance()->getModuleNameFromClassName($class);
            $rClass = new ReflectionClass($class);

            if (!Piwik::hasUserSuperUserAccess() && $this->checkIfClassCommentContainsHideAnnotation($rClass)) {
                continue;
            }

            if ($this->checkIfCommentContainsInternalAnnotation($rClass)) {
                continue;
            }

            $toDisplay = $this->prepareModulesAndMethods($info, $moduleName);

            foreach ($toDisplay as $moduleName => $methods) {
                foreach ($methods as $index => $method) {
                    if (!method_exists($class, $method)) { // method is handled through API.Request.intercept event
                        continue;
                    }

                    $reflectionMethod = new \ReflectionMethod($class, $method);
                    if ($this->checkIfCommentContainsInternalAnnotation($reflectionMethod)) {
                        unset($toDisplay[$moduleName][$index]);
                    }
                }
                if (empty($toDisplay[$moduleName])) {
                    unset($toDisplay[$moduleName]);
                }
            }

            foreach ($toDisplay as $moduleName => $methods) {
                $toc .= $this->prepareModuleToDisplay($moduleName);
                $str .= $this->prepareMethodToDisplay($moduleName, $info, $methods, $class, $outputExampleUrls, $prefixUrls, $displayTitlesAsAngularDirective);
            }
        }
        return array($toc, $str);
    }
}

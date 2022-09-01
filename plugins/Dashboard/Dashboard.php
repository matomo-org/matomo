<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Category\Subcategory;
use Piwik\Widget\WidgetConfig;
use Piwik\Plugin;

/**
 */
class Dashboard extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'UsersManager.deleteUser'                => 'deleteDashboardLayout',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Widget.addWidgetConfigs'                => 'addWidgetConfigs',
            'Category.addSubcategories'              => 'addSubcategories',
            'Widgetize.shouldEmbedIframeEmpty'       => 'shouldEmbedIframeEmpty',
            'Db.getTablesInstalled'                  => 'getTablesInstalled'
        );
    }

    /**
     * Register the new tables, so Matomo knows about them.
     *
     * @param array $allTablesInstalled
     */
    public function getTablesInstalled(&$allTablesInstalled)
    {
        $allTablesInstalled[] = Common::prefixTable('user_dashboard');
    }

    public function shouldEmbedIframeEmpty(&$shouldEmbedEmpty, $controllerName, $actionName)
    {
        if ($controllerName == 'Dashboard' && $actionName == 'index') {
            $shouldEmbedEmpty = true;
        }
    }

    public function addWidgetConfigs(&$widgets)
    {
        if (Piwik::isUserIsAnonymous()) {
            $this->addDefaultDashboard($widgets);
        } else {
            $dashboards = $this->getDashboards();

            if (empty($dashboards)) {
                $this->addDefaultDashboard($widgets);
            } else {
                foreach ($dashboards as $dashboard) {
                    $config = new WidgetConfig();
                    $config->setIsNotWidgetizable();
                    $config->setModule('Dashboard');
                    $config->setAction('embeddedIndex');
                    $config->setCategoryId('Dashboard_Dashboard');
                    $config->setSubcategoryId($dashboard['id']);
                    $config->setParameters(array('idDashboard' => $dashboard['id']));
                    $widgets[] = $config;
                }
            }
        }
    }

    private function getDashboards()
    {
        return Request::processRequest('Dashboard.getDashboards',
            ['filter_limit' => '-1', 'filter_offset' => 0],
            []
        );
    }

    private function addDefaultDashboard(&$widgets)
    {
        $config = new WidgetConfig();
        $config->setIsNotWidgetizable();
        $config->setModule('Dashboard');
        $config->setAction('embeddedIndex');
        $config->setCategoryId('Dashboard_Dashboard');
        $config->setSubcategoryId('1');
        $config->setParameters(array('idDashboard' => 1));
        $widgets[] = $config;
    }

    public function addSubcategories(&$subcategories)
    {
        if (Piwik::isUserIsAnonymous()) {
            $this->addDefaultSubcategory($subcategories);
        } else {
            $dashboards = $this->getDashboards();

            if (empty($dashboards)) {
                $this->addDefaultSubcategory($subcategories);
            } else {
                $order = 0;
                foreach ($dashboards as $dashboard) {
                    $subcategory = new Subcategory();
                    $subcategory->setName($dashboard['name']);
                    $subcategory->setCategoryId('Dashboard_Dashboard');
                    $subcategory->setId($dashboard['id']);
                    $subcategory->setOrder($order++);
                    $subcategories[] = $subcategory;
                }
            }
        }
    }

    private function addDefaultSubcategory(&$subcategories)
    {
        $subcategory = new Subcategory();
        $subcategory->setName('Dashboard_Dashboard');
        $subcategory->setCategoryId('Dashboard_Dashboard');
        $subcategory->setId('1');
        $subcategory->setOrder(1);
        $subcategories[] = $subcategory;
    }

    /**
     * Returns the layout in the DB for the given user, or false if the layout has not been set yet.
     * Parameters must be checked BEFORE this function call
     *
     * @param string $login
     * @param int $idDashboard
     *
     * @return bool|string
     */
    public function getLayoutForUser($login, $idDashboard)
    {
        $return = $this->getModel()->getLayoutForUser($login, $idDashboard);

        if (count($return) == 0) {
            return false;
        }

        return $return[0]['layout'];
    }

    private function getModel()
    {
        return new Model();
    }

    public function getDefaultLayout()
    {
        $defaultLayout = $this->getLayoutForUser('', 1);

        if (empty($defaultLayout)) {
            $pluginManager = Plugin\Manager::getInstance();

            $advertisingWidget = '';
            $advertising = StaticContainer::get('Piwik\ProfessionalServices\Advertising');
            if ($advertising->areAdsForProfessionalServicesEnabled() && $pluginManager->isPluginActivated('ProfessionalServices')) {
                $advertisingWidget = '{"uniqueId":"widgetProfessionalServicespromoServices","parameters":{"module":"ProfessionalServices","action":"promoServices"}},';
            }
            $piwikPromoWidget = '{"uniqueId":"widgetCoreHomegetPromoVideo","parameters":{"module":"CoreHome","action":"getPromoVideo"}}';
            $insightsWidget = '';
            if ($pluginManager->isPluginActivated('Insights')) {
                $insightsWidget = '{"uniqueId":"widgetInsightsgetOverallMoversAndShakers","parameters":{"module":"Insights","action":"getOverallMoversAndShakers"}},';
            }
            $defaultLayout = '[
                [
                    {"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}},
                    ' . $piwikPromoWidget . '
                ],
                [
                    {"uniqueId":"widgetVisitsSummarygetEvolutionGraphforceView1viewDataTablegraphEvolution","parameters":{"forceView":"1","viewDataTable":"graphEvolution","module":"VisitsSummary","action":"getEvolutionGraph"}},
                    ' . $advertisingWidget . '
                    ' . $insightsWidget . '
                    {"uniqueId":"widgetVisitsSummarygetforceView1viewDataTablesparklines","parameters":{"forceView":"1","viewDataTable":"sparklines","module":"VisitsSummary","action":"get"}}
                ],
                [
                    {"uniqueId":"widgetUserCountryMapvisitorMap","parameters":{"module":"UserCountryMap","action":"visitorMap"}},
                    {"uniqueId":"widgetReferrersgetReferrerType","parameters":{"module":"Referrers","action":"getReferrerType"}},
                    {"uniqueId":"widgetRssWidgetrssPiwik","parameters":{"module":"RssWidget","action":"rssPiwik"}}
                ]
            ]';
        }

        /**
         * Allows other plugins to modify the default dashboard layout.
         *
         * @param string &$defaultLayout JSON encoded string of the default dashboard layout. Contains an
         *                               array of columns where each column is an array of widgets. Each
         *                               widget is an associative array w/ the following elements:
         *
         *                               * **uniqueId**: The widget's unique ID.
         *                               * **parameters**: The array of query parameters that should be used to get this widget's report.
         */
        Piwik::postEvent("Dashboard.changeDefaultDashboardLayout", array(&$defaultLayout));

        $defaultLayout = $this->removeDisabledPluginFromLayout($defaultLayout);

        return $defaultLayout;
    }

    public function getAllDashboards($login)
    {
        $dashboards = $this->getModel()->getAllDashboardsForUser($login);

        $nameless = 1;
        foreach ($dashboards as &$dashboard) {

            if (empty($dashboard['name'])) {
                $dashboard['name'] = Piwik::translate('Dashboard_DashboardOf', $login);
                if ($nameless > 1) {
                    $dashboard['name'] .= " ($nameless)";
                }

                $nameless++;
            }

            $dashboard['name'] = Common::unsanitizeInputValue($dashboard['name']);

            $layout = '[]';
            if (!empty($dashboard['layout'])) {
                $layout = $dashboard['layout'];
            }

            $dashboard['layout'] = $this->decodeLayout($layout);
        }

        return $dashboards;
    }

    private function isAlreadyDecodedLayout($layout)
    {
        return !is_string($layout);
    }

    public function removeDisabledPluginFromLayout($layout)
    {
        $layoutObject = $this->decodeLayout($layout);

        // if the json decoding works (ie. new Json format)
        // we will only return the widgets that are from enabled plugins

        if (is_array($layoutObject)) {
            $layoutObject = (object)array(
                'config'  => array('layout' => '33-33-33'),
                'columns' => $layoutObject
            );
        }

        if (empty($layoutObject) || empty($layoutObject->columns)) {
            $layoutObject = (object)array(
                'config'  => array('layout' => '33-33-33'),
                'columns' => array()
            );
        }

        $layout = $this->encodeLayout($layoutObject);
        return $layout;
    }

    public function decodeLayout($layout)
    {
        if ($this->isAlreadyDecodedLayout($layout)) {
            return $layout;
        }

        $layout = html_entity_decode($layout, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        $layout = str_replace("\\\"", "\"", $layout);
        $layout = str_replace("\n", "", $layout);

        return json_decode($layout, $assoc = false);
    }

    public function encodeLayout($layout)
    {
        return json_encode($layout);
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Dashboard/javascripts/widgetMenu.js";
        $jsFiles[] = "plugins/Dashboard/javascripts/dashboardObject.js";
        $jsFiles[] = "plugins/Dashboard/javascripts/dashboardWidget.js";
        $jsFiles[] = "plugins/Dashboard/javascripts/dashboard.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/CoreHome/stylesheets/dataTable.less";
        $stylesheets[] = "plugins/Dashboard/stylesheets/dashboard.less";
        $stylesheets[] = "plugins/Dashboard/stylesheets/widget.less";
    }

    public function deleteDashboardLayout($userLogin)
    {
        $this->getModel()->deleteAllLayoutsForUser($userLogin);
    }

    public function install()
    {
        Model::install();
    }

    public function uninstall()
    {
        Model::uninstall();
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Dashboard_AddPreviewedWidget';
        $translationKeys[] = 'Dashboard_WidgetPreview';
        $translationKeys[] = 'Dashboard_Maximise';
        $translationKeys[] = 'Dashboard_Minimise';
        $translationKeys[] = 'Dashboard_LoadingWidget';
        $translationKeys[] = 'Dashboard_WidgetNotFound';
        $translationKeys[] = 'Dashboard_DashboardCopied';
        $translationKeys[] = 'Dashboard_Dashboard';
        $translationKeys[] = 'Dashboard_RemoveDefaultDashboardNotPossible';
        $translationKeys[] = 'General_Close';
        $translationKeys[] = 'General_HelpResources';
        $translationKeys[] = 'General_Refresh';
    }
}

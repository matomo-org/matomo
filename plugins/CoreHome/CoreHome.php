<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome;

/**
 *
 */
class CoreHome extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "libs/jquery/themes/base/jquery-ui.css";
        $stylesheets[] = "libs/jquery/stylesheets/jquery.jscrollpane.css";
        $stylesheets[] = "libs/jquery/stylesheets/scroll.less";
        $stylesheets[] = "plugins/Morpheus/stylesheets/base.less";
        $stylesheets[] = "plugins/Morpheus/stylesheets/theme.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/coreHome.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/menu.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/dataTable.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/cloud.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/jquery.ui.autocomplete.css";
        $stylesheets[] = "plugins/CoreHome/stylesheets/jqplotColors.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/sparklineColors.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/promo.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/color_manager.css";
        $stylesheets[] = "plugins/CoreHome/stylesheets/sparklineColors.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/notification.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/zen-mode.less";
        $stylesheets[] = "plugins/CoreHome/angularjs/enrichedheadline/enrichedheadline.less";
        $stylesheets[] = "plugins/CoreHome/angularjs/menudropdown/menudropdown.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "libs/jquery/jquery.js";
        $jsFiles[] = "libs/jquery/jquery-ui.js";
        $jsFiles[] = "libs/jquery/jquery.browser.js";
        $jsFiles[] = "libs/jquery/jquery.truncate.js";
        $jsFiles[] = "libs/jquery/jquery.scrollTo.js";
        $jsFiles[] = "libs/jquery/jquery.history.js";
        $jsFiles[] = "libs/jquery/jquery.jscrollpane.js";
        $jsFiles[] = "libs/jquery/jquery.mousewheel.js";
        $jsFiles[] = "libs/jquery/mwheelIntent.js";
        $jsFiles[] = "libs/javascript/sprintf.js";
        $jsFiles[] = "libs/mousetrap/mousetrap.min.js";
        $jsFiles[] = "libs/angularjs/angular.min.js";
        $jsFiles[] = "libs/angularjs/angular-sanitize.min.js";
        $jsFiles[] = "libs/angularjs/angular-animate.min.js";
        $jsFiles[] = "libs/angularjs/angular-cookies.min.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/piwikHelper.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/ajaxHelper.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/jquery.icheck.min.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/morpheus.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/require.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/uiControl.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/dataTable.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/dataTable_rowactions.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/popover.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/broadcast.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/menu.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/menu_init.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/calendar.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/sparkline.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/corehome.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/top_controls.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/donate.js";
        $jsFiles[] = "libs/jqplot/jqplot-custom.min.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/color_manager.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/notification.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/notification_parser.js";

        $jsFiles[] = "plugins/CoreHome/angularjs/piwikAppConfig.js";

        $jsFiles[] = "plugins/CoreHome/angularjs/common/services/service.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/services/piwik.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/services/piwik-api.js";

        $jsFiles[] = "plugins/CoreHome/angularjs/common/filters/filter.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/filters/translate.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/filters/startfrom.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/filters/evolution.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/filters/length.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/filters/trim.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/filters/pretty-url.js";

        $jsFiles[] = "plugins/CoreHome/angularjs/common/directives/directive.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/directives/autocomplete-matched.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/directives/focus-anywhere-but-here.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/directives/ignore-click.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/directives/onenter.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/directives/focusif.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/common/directives/dialog.js";

        $jsFiles[] = "plugins/CoreHome/angularjs/piwikApp.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/anchorLinkFix.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/http404check.js";

        $jsFiles[] = "plugins/CoreHome/angularjs/siteselector/siteselector-model.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/siteselector/siteselector-controller.js";
        $jsFiles[] = "plugins/CoreHome/angularjs/siteselector/siteselector-directive.js";

        $jsFiles[] = "plugins/CoreHome/angularjs/menudropdown/menudropdown-directive.js";

        $jsFiles[] = "plugins/CoreHome/angularjs/enrichedheadline/enrichedheadline-directive.js";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'General_InvalidDateRange';
        $translationKeys[] = 'General_Loading';
        $translationKeys[] = 'General_Show';
        $translationKeys[] = 'General_Hide';
        $translationKeys[] = 'General_YearShort';
        $translationKeys[] = 'General_MultiSitesSummary';
        $translationKeys[] = 'CoreHome_YouAreUsingTheLatestVersion';
        $translationKeys[] = 'CoreHome_IncludeRowsWithLowPopulation';
        $translationKeys[] = 'CoreHome_ExcludeRowsWithLowPopulation';
        $translationKeys[] = 'CoreHome_DataTableIncludeAggregateRows';
        $translationKeys[] = 'CoreHome_DataTableExcludeAggregateRows';
        $translationKeys[] = 'CoreHome_Default';
        $translationKeys[] = 'CoreHome_PageOf';
        $translationKeys[] = 'CoreHome_FlattenDataTable';
        $translationKeys[] = 'CoreHome_UnFlattenDataTable';
        $translationKeys[] = 'CoreHome_ExternalHelp';
        $translationKeys[] = 'SitesManager_NotFound';
        $translationKeys[] = 'Annotations_ViewAndAddAnnotations';
        $translationKeys[] = 'General_RowEvolutionRowActionTooltipTitle';
        $translationKeys[] = 'General_RowEvolutionRowActionTooltip';
        $translationKeys[] = 'Annotations_IconDesc';
        $translationKeys[] = 'Annotations_IconDescHideNotes';
        $translationKeys[] = 'Annotations_HideAnnotationsFor';
        $translationKeys[] = 'General_LoadingPopover';
        $translationKeys[] = 'General_LoadingPopoverFor';
        $translationKeys[] = 'General_ShortMonth_1';
        $translationKeys[] = 'General_ShortMonth_2';
        $translationKeys[] = 'General_ShortMonth_3';
        $translationKeys[] = 'General_ShortMonth_4';
        $translationKeys[] = 'General_ShortMonth_5';
        $translationKeys[] = 'General_ShortMonth_6';
        $translationKeys[] = 'General_ShortMonth_7';
        $translationKeys[] = 'General_ShortMonth_8';
        $translationKeys[] = 'General_ShortMonth_9';
        $translationKeys[] = 'General_ShortMonth_10';
        $translationKeys[] = 'General_ShortMonth_11';
        $translationKeys[] = 'General_ShortMonth_12';
        $translationKeys[] = 'General_LongMonth_1';
        $translationKeys[] = 'General_LongMonth_2';
        $translationKeys[] = 'General_LongMonth_3';
        $translationKeys[] = 'General_LongMonth_4';
        $translationKeys[] = 'General_LongMonth_5';
        $translationKeys[] = 'General_LongMonth_6';
        $translationKeys[] = 'General_LongMonth_7';
        $translationKeys[] = 'General_LongMonth_8';
        $translationKeys[] = 'General_LongMonth_9';
        $translationKeys[] = 'General_LongMonth_10';
        $translationKeys[] = 'General_LongMonth_11';
        $translationKeys[] = 'General_LongMonth_12';
        $translationKeys[] = 'General_ShortDay_1';
        $translationKeys[] = 'General_ShortDay_2';
        $translationKeys[] = 'General_ShortDay_3';
        $translationKeys[] = 'General_ShortDay_4';
        $translationKeys[] = 'General_ShortDay_5';
        $translationKeys[] = 'General_ShortDay_6';
        $translationKeys[] = 'General_ShortDay_7';
        $translationKeys[] = 'General_LongDay_1';
        $translationKeys[] = 'General_LongDay_2';
        $translationKeys[] = 'General_LongDay_3';
        $translationKeys[] = 'General_LongDay_4';
        $translationKeys[] = 'General_LongDay_5';
        $translationKeys[] = 'General_LongDay_6';
        $translationKeys[] = 'General_LongDay_7';
        $translationKeys[] = 'General_DayMo';
        $translationKeys[] = 'General_DayTu';
        $translationKeys[] = 'General_DayWe';
        $translationKeys[] = 'General_DayTh';
        $translationKeys[] = 'General_DayFr';
        $translationKeys[] = 'General_DaySa';
        $translationKeys[] = 'General_DaySu';
        $translationKeys[] = 'General_Search';
        $translationKeys[] = 'General_MoreDetails';
        $translationKeys[] = 'General_Help';
        $translationKeys[] = 'General_MoreDetails';
        $translationKeys[] = 'General_Help';
        $translationKeys[] = 'General_Id';
        $translationKeys[] = 'General_Name';
        $translationKeys[] = 'General_JsTrackingTag';
        $translationKeys[] = 'General_Yes';
        $translationKeys[] = 'General_No';
        $translationKeys[] = 'General_Edit';
        $translationKeys[] = 'General_Delete';
        $translationKeys[] = 'General_Default';
        $translationKeys[] = 'General_LoadingData';
    }
}

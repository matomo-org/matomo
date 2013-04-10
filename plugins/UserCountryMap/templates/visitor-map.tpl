<div class="UserCountryMap" style="position:relative; overflow:hidden;">
    <div class="UserCountryMap_container">
        <div class="UserCountryMap_map" style="overflow:hidden"></div>
        <div class="UserCountryMap-overlay UserCountryMap-title">
            <div class="content">
                <!--<div class="map-title" style="font-weight:bold; color:#9A9386;"></div>-->
                <div class="map-stats" style="color:#565656;"><b></b></div>
            </div>
        </div>
        <div class="UserCountryMap-overlay UserCountryMap-legend">
            <div class="content">
            </div>
        </div>
        <div class="UserCountryMap-tooltip UserCountryMap-info">
            <div foo="bar" class="content unlocated-stats" data-tpl="{'UserCountryMap_Unlocated'|translate}">
            </div>
        </div>
        <div class="UserCountryMap-info-btn" data-tooltip-target=".UserCountryMap-tooltip"></div>
    </div>
    <div class="mapWidgetStatus">
        {if $noData }
            <div class="pk-emptyDataTable">{'CoreHome_ThereIsNoDataForThisReport'|translate}</div>
        {else}
            <span class="loadingPiwik">
            <img src="{$piwikUrl}themes/default/images/loading-blue.gif"> {'General_LoadingData'|translate}...
        </span>
        {/if}
    </div>
    <div class="dataTableFeatures" style="padding-top:0px;">
        <div class="dataTableFooterIcons">
            <div class="dataTableFooterWrap" var="graphVerticalBar">
                <img class="UserCountryMap-activeItem dataTableFooterActiveItem" src="{$piwikUrl}themes/default/images/data_table_footer_active_item.png"
                     style="left: 25px;">

                <div class="tableIconsGroup">
                    <span class="tableAllColumnsSwitch">
                        <a class="UserCountryMap-btn-zoom tableIcon" format="table"><img src="{$piwikUrl}plugins/UserCountryMap/img/zoom-out.png"
                                                                                         title="Zoom to world"></a>
                    </span>
                </div>
                <div class="tableIconsGroup UserCountryMap-view-mode-buttons">
                    <span class="tableAllColumnsSwitch">
                        <a var="tableAllColumns" class="UserCountryMap-btn-region tableIcon activeIcon" format="tableAllColumns"
                           data-region="{'UserCountryMap_Regions'|translate}" data-country="{'UserCountryMap_Countries'|translate}"><img
                                    src="{$piwikUrl}plugins/UserCountryMap/img/regions.png" title="Show vistors per region/country"> <span
                                    style="margin:0">{'UserCountryMap_Countries'|translate}</span>&nbsp;</a>
                        <a var="tableGoals" class="UserCountryMap-btn-city tableIcon inactiveIco" format="tableGoals"><img
                                    src="{$piwikUrl}plugins/UserCountryMap/img/cities.png" title="Show visitors per city"> <span
                                    style="margin:0">{'UserCountryMap_Cities'|translate}</span>&nbsp;</a>
                    </span>
                </div>

            </div>

            <select class="userCountryMapSelectMetrics" style="float:right;margin-right:0;margin-bottom:5px;max-width: 9em;font-size:10px">
                {foreach from=$metrics item=metric}
                    <option value="{$metric[0]}" {if $metric[0] == $defaultMetric}selected="selected"{/if}>{$metric[1]}</option>
                {/foreach}
            </select>
            <select class="userCountryMapSelectCountry" style="float:right;margin-right:5px;margin-bottom:5px; max-width: 9em;font-size:10px">
                <option value="world">{'UserCountryMap_WorldWide'|translate}</option>
                <option disabled="disabled">––––––</option>
                <option value="AF">{'UserCountry_continent_afr'|translate}</option>
                <option value="AS">{'UserCountry_continent_asi'|translate}</option>
                <option value="EU">{'UserCountry_continent_eur'|translate}</option>
                <option value="NA">{'UserCountry_continent_amn'|translate}</option>
                <option value="OC">{'UserCountry_continent_oce'|translate}</option>
                <option value="SA">{'UserCountry_continent_ams'|translate}</option>
                <option disabled="disabled">––––––</option>
            </select>
        </div>
    </div>
</div>

{if !$noData }

    <!-- configure some piwik vars -->
    <script type="text/javascript">

        var visitorMap,
                config = JSON.parse('{$config|escape:'javascript'}');
        config._ = JSON.parse('{$localeJSON|escape:'javascript'}');
        config.reqParams = JSON.parse('{$reqParamsJSON|escape:'javascript'}');

        $('.UserCountryMap').addClass('dataTable');


        {literal}
        if ($('#dashboardWidgetsArea').length) {
            // dashboard mode
            var $widgetContent = $('.UserCountryMap').parents('.widgetContent');

            $widgetContent.on('widget:create',function (evt, widget) {
                visitorMap = new UserCountryMap.VisitorMap(config, widget);
            }).on('widget:maximise',function (evt) {
                        visitorMap.resize();
                    }).on('widget:minimise',function (evt) {
                        visitorMap.resize();
                    }).on('widget:destroy', function (evt) {
                        visitorMap.destroy();
                    });
        } else {
            // stand-alone mode
            visitorMap = new UserCountryMap.VisitorMap(config);
        }
        {/literal}

    </script>
{/if}

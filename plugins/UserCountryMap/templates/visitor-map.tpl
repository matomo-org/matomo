<div id="UserCountryMap" style="position:relative; overflow:hidden;">
    <div id="UserCountryMap_container">
        <div id="UserCountryMap_map" style="overflow:hidden"></div>
        <div class="UserCountryMap-overlay UserCountryMap-title">
            <div class="content">
                <!--<div class="map-title" style="font-weight:bold; color:#9A9386;"></div>-->
                <div class="map-stats" style="color:#565656;"><b></b> </div>
            </div>
        </div>
        <div class="UserCountryMap-overlay UserCountryMap-legend">
            <div class="content">
            </div>
        </div>
        <div class="UserCountryMap-tooltip UserCountryMap-info">
            <div class="content unlocated-stats" data-tpl="{'UserCountryMap_Unlocated'|translate}" >
            </div>
        </div>
        <div class="UserCountryMap-info-btn" data-tooltip-target=".UserCountryMap-tooltip"></div>
    </div>
    <div class="mapWidgetStatus">
        <span class="loadingPiwik">
            <img src="{$piwikUrl}themes/default/images/loading-blue.gif"> {'General_LoadingData'|translate}...
        </span>
        <span class="noDataForReport" style="display:none">
            {'CoreHome_ThereIsNoDataForThisReport'|translate}...
        </span>
    </div>
    <div class="dataTableFeatures" style="padding-top:0px;">
        <div class="dataTableFooterIcons">
            <div class="dataTableFooterWrap" var="graphVerticalBar">
                <img id="UserCountryMap-activeItem" class="dataTableFooterActiveItem" src="{$piwikUrl}themes/default/images/data_table_footer_active_item.png" style="left: 25px;">

                <div class="tableIconsGroup">
                    <span class="tableAllColumnsSwitch">
                        <a id="UserCountryMap-btn-zoom" format="table" class="tableIcon"><img src="{$piwikUrl}plugins/UserCountryMap/img/zoom-out.png" title="Zoom to world"></a>
                    </span>
                </div>
                <div class="tableIconsGroup" id="UserCountryMap-view-mode-buttons">
                    <span class="tableAllColumnsSwitch">
                        <a var="tableAllColumns" id="UserCountryMap-btn-region" format="tableAllColumns" class="tableIcon activeIcon" data-region="{'UserCountryMap_Regions'|translate}" data-country="{'UserCountryMap_Countries'|translate}"><img src="{$piwikUrl}plugins/UserCountryMap/img/regions.png" title="Show vistors per region/country"> <span style="margin:0">{'UserCountryMap_Countries'|translate}</span>&nbsp;</a>
                        <a var="tableGoals" id="UserCountryMap-btn-city" format="tableGoals" class="tableIcon inactiveIcon"><img src="{$piwikUrl}plugins/UserCountryMap/img/cities.png" title="Show visitors per city"> <span style="margin:0">{'UserCountryMap_Cities'|translate}</span>&nbsp;</a>
                    </span>
                </div>

            </div>

           <select id="userCountryMapSelectMetrics" style="float:right;margin-right:0;margin-bottom:5px;max-width: 9em;font-size:10px">
                {foreach from=$metrics item=metric}
                    <option value="{$metric[0]}" {if $metric[0] == $defaultMetric}selected="selected"{/if}>{$metric[1]}</option>
                {/foreach}
            </select>
            <select id="userCountryMapSelectCountry" style="float:right;margin-right:5px;margin-bottom:5px; max-width: 9em;font-size:10px">
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

<!-- configure some piwik vars -->
<script type="text/javascript">

    var config = JSON.parse('{$config}');
    UserCountryMap._ = JSON.parse('{$localeJSON}');
    UserCountryMap.reqParams = JSON.parse('{$reqParamsJSON}');

    $('#UserCountryMap').addClass('dataTable');

{literal}
    setTimeout(function() { UserCountryMap.run(config) }, 1000);
{/literal}

</script>


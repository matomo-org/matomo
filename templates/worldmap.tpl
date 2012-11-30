<div id="UserCountryMap" style="position:relative; overflow:hidden;">
    <style type="text/css">
{literal}
    /* this should me moved to TableView css sometimes */
    .dataTableFooterIcons .inactiveIcon:hover {
        background-color: #F2F1ED;
    }
    .dataTableFooterIcons .inactiveIcon {
        cursor: default;
    }
    .dataTableFooterIcons .inactiveIcon img {
        opacity: 0.3;
        -moz-opacity: 0.3;
        filter:alpha(opacity=3);
    }
{/literal}
    </style>
    <div id="UserCountryMap_container">
        <style type="text/css">
{literal}
#UserCountryMap_overlay {
    display:block;
    position: absolute;
    top: 5px;
    left:5px;
    z-index:1000;
}

#UserCountryMap_overlay .content {
    padding:5px;
    border-radius:3px;
    background:rgba(255,255,255,0.9);
}
{/literal}
        </style>
        <div id="UserCountryMap_map" style="overflow:hidden"></div>
        <div id="UserCountryMap_overlay">
            <div class="content">
                <div class="map-title" style="font-weight:bold; color:#9A9386;"></div>
                <div class="map-stats" style="color:#565656;"><b></b> </div>
            </div>
        </div>
    </div>
    <div class="dataTableFeatures" style="padding-top:0px">
        <span class="loadingPiwik">
            <img src="{$piwikUrl}themes/default/images/loading-blue.gif"> {'General_LoadingData'|translate}...
        </span>
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
                        <a var="tableAllColumns" id="UserCountryMap-btn-region" format="tableAllColumns" class="tableIcon activeIcon" data-region="{'UserCountry_Region'|translate}" data-country="{'UserCountry_Country'|translate}"><img src="{$piwikUrl}plugins/UserCountryMap/img/regions.png" title="Show vistors per region/country"> <span style="margin:0">{'UserCountry_Country'|translate}</span>&nbsp;</a>
                        <a var="tableGoals" id="UserCountryMap-btn-city" format="tableGoals" class="tableIcon inactiveIcon"><img src="{$piwikUrl}plugins/UserCountryMap/img/cities.png" title="Show visitors per city"> {'UserCountry_City'|translate}&nbsp;</a>
                    </span>
                </div>

            </div>

           <select id="userCountryMapSelectMetrics" style="float:right;margin-right:0;margin-bottom:5px;max-width: 10em;font-size:10px">
                {foreach from=$metrics item=metric}
                    <option value="{$metric[0]}" {if $metric[0] == $defaultMetric}selected="selected"{/if}>{$metric[1]}</option>
                {/foreach}
            </select>

            <select id="userCountryMapSelectCountry" style="float:right;margin-right:5px;margin-bottom:5px; max-width: 12em;font-size:10px">
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

            <!--<div id="userCountryMapFlag" style="float:right;width:25px;height: 25px">

            </div>-->
        </div>
    </div>
</div>

<!-- configure some piwik vars -->
<script type="text/javascript">

{literal}
    var config = { metrics: {} };
{/literal}

    config.mapCssPath = "{$piwikUrl}plugins/UserCountryMap/css/map.css";
    config.svgBasePath = "{$piwikUrl}plugins/UserCountryMap/svg/";
    config.countryDataUrl = "{$countryDataUrl}";
    config.regionDataUrl = "{$regionDataUrl}";
    config.cityDataUrl = "{$cityDataUrl}";
    config.visitsSummary = JSON.parse('{$visitsSummary}');
    {foreach from=$metrics item=metric}
    config.metrics['{$metric[0]}'] = "{$metric[1]}";
    {/foreach}

{literal}
    $(function() {
        UserCountryMap.run(config)
    });
{/literal}

</script>

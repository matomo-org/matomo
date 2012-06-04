<div id="UserCountryMap_content" style="position:relative; overflow:hidden;">
    <div id="UserCountryMap_container">
        <div id="UserCountryMap_map" style="overflow:hidden"></div>
        <div id="UserCountryMap_overlay" style="position: absolute; top: 10px; left:10px;z-index:1000;padding:5px;border-radius:3px;background:rgba(255,255,255,0.9)">
            <div class="county-name" style="font-weight:bold; color:#9A9386;">Deutschland</div>
            <div class="county-stats" style="color:#565656;"><b>1.234</b> Visits total</div>
        </div>
    </div>
    <div class="dataTableFeatures" style="padding-top:0px">
        <span class="loadingPiwik">
            <img src="{$piwikUrl}themes/default/images/loading-blue.gif"> Loading data...
        </span>
        <div class="dataTableFooterIcons">
            <div class="dataTableFooterWrap" var="graphVerticalBar">
                <img class="dataTableFooterActiveItem" src="{$piwikUrl}themes/default/images/data_table_footer_active_item.png" style="left: 67px;">

                <div class="tableIconsGroup">
                    <span class="tableAllColumnsSwitch">
                        <a var="world" format="table" class="tableIcon activeIcon"><img src="{$piwikUrl}plugins/UserCountryMap/img/zoom-out.png" title="Zoom to world"></a>
                    </span>
                </div>

                <div class="tableIconsGroup">
                    <span class="tableAllColumnsSwitch">
                        <a var="world" format="table" class="tableIcon activeIcon"><img src="{$piwikUrl}plugins/UserCountryMap/img/world.png" title="Zoom to world"></a>
                        <a var="tableAllColumns" format="tableAllColumns" class="tableIcon"><img src="{$piwikUrl}plugins/UserCountryMap/img/regions.png" title="Display a table with more metrics"></a>
                        <a var="tableGoals" format="tableGoals" class="tableIcon"><img src="{$piwikUrl}plugins/UserCountryMap/img/cities.png" title="Display a table with Goals metrics"></a>
                    </span>
                </div>

            </div>

           <select id="userCountryMapSelectMetrics" style="float:right;margin-right:0;margin-bottom:5px">
                {foreach from=$metrics item=metric}
                    <option value="{$metric[0]}" {if $metric[0] == $defaultMetric}selected="selected"{/if}>{$metric[1]}</option>
                {/foreach}
            </select>

            <select id="userCountryMapSelectCountry" style="float:right;margin-right:5px;margin-bottom:5px">
                
            </select>
        </div>
    </div>
</div>

<!-- configure some piwik vars -->
<script type="text/javascript">

{literal}
    var config = {};
{/literal}

    config.mapCssPath = "{$piwikUrl}plugins/UserCountryMap/css/map.css";
    config.svgBasePath = "{$piwikUrl}plugins/UserCountryMap/svg/";
    config.countryDataUrl = "{$countryDataUrl}";

{literal}
    $(function() {
        UserCountryMap.run(config)
    });
{/literal}

</script>

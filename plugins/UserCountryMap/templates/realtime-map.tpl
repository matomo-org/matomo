<div id="RealTimeMap" style="position:relative; overflow:hidden;">

    <div id="RealTimeMap_container">
        <div id="RealTimeMap_map" style="overflow:hidden"></div>
        <div class="realTimeMap_overlay">
            <span class="showing_visits_of" style="display:none">{'UserCountryMap_ShowingVisits'|translate} <span class="realTimeMap_timeSpan"
                                                                                                                  style="font-weight:bold"></span></span>
            <span class="no_data" style="display:none">{'CoreHome_ThereIsNoDataForThisReport'|translate}</span>
            <span class="loading_data">{'General_LoadingData'|translate}...</span>
            <img src="{$piwikUrl}plugins/UserCountryMap/img/realtimemap-loading.gif" style="vertical-align:baseline;position:relative;left:-2px;">
        </div>
        <div class="realTimeMap_overlay realTimeMap_datetime"></div>
    </div>
    <div id="RealTimeMap_meta">
        <span class="loadingPiwik">
            <img src="{$piwikUrl}themes/default/images/loading-blue.gif"> {'General_LoadingData'|translate}...
        </span>
    </div>

</div>

<!-- configure some piwik vars -->
<script type="text/javascript">

    {* If the map is loaded from the menu, do a few tweaks to clean up the display *}
    {if $mapIsStandaloneNotWidget}
    function initStandaloneMap() {ldelim}
        $('.top_controls').hide();
        $('ul.nav').on('piwikSwitchPage', function (event, item) {ldelim}
            var clickedMenuIsNotMap = ($(item).text() != "{'UserCountryMap_RealTimeMap'|translate|escape:'js'}");
            if (clickedMenuIsNotMap) {ldelim}
                $('.top_controls').show();
                {rdelim
            }
            {rdelim
        });
        $('.realTimeMap_overlay').css('top', '0px');
        $('.realTimeMap_datetime').css('top', '20px');
        {rdelim
    }

    initStandaloneMap();
    {/if}

    {literal}
    var config = { metrics: {} };
    {/literal}

    config.svgBasePath = "{$piwikUrl}plugins/UserCountryMap/svg/";
    config.liveRefreshAfterMs = {$liveRefreshAfterMs};

    config._ = JSON.parse('{$localeJSON|escape:'javascript'}');
    config.reqParams = JSON.parse('{$reqParamsJSON|escape:'javascript'}');
    config.siteHasGoals = {$hasGoals};
    config.maxVisits = {$maxVisits};

    var realtimeMap;

    {literal}
    if ($('#dashboardWidgetsArea').length) {
        // dashboard mode
        var $widgetContent = $('#RealTimeMap').parents('.widgetContent');

        $widgetContent.on('widget:create',function (evt, widget) {
            realtimeMap = new UserCountryMap.RealtimeMap(config, widget);
        }).on('widget:maximise',function (evt) {
                    realtimeMap.resize();
                }).on('widget:minimise',function (evt) {
                    realtimeMap.resize();
                }).on('widget:destroy', function (evt) {
                    realtimeMap.destroy();
                });
    } else {
        // stand-alone mode
        realtimeMap = new UserCountryMap.RealtimeMap(config);
    }
    {/literal}

</script>

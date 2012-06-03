<div id="UserCountryMap_content" style="position:relative; overflow:hidden;">
    <div id="UserCountryMap_container">
        <div id="UserCountryMap_map"></div>
        <div id="UserCountryMap_overlay" style="position: absolute; top: 10px; left:10px;">
            <div class="county-name" style="font-weight:bold; color:#9A9386;">Deutschland</div>
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
                        <a var="world" format="table" class="tableIcon activeIcon"><img src="{$piwikUrl}plugins/UserCountryMap/img/world.png" title="Zoom to world"></a>
                        <a var="tableAllColumns" format="tableAllColumns" class="tableIcon"><img src="{$piwikUrl}plugins/UserCountryMap/img/regions.png" title="Display a table with more metrics"></a>
                        <a var="tableGoals" format="tableGoals" class="tableIcon"><img src="{$piwikUrl}plugins/UserCountryMap/img/cities.png" title="Display a table with Goals metrics"></a>
                    </span>
                </div>

            </div>
           
            <input id="userCountryMapInsertID" style="float:left; width:5em" placeholder="country code" />
            <button id="userCountryMap-update" style="float:left">update</button>

            <select id="userCountryMapSelectMetrics" style="float:right;margin-right:0;margin-bottom:5px">
                {foreach from=$metrics item=metric}
                    <option value="{$metric[0]}" {if $metric[0] == $defaultMetric}selected="selected"{/if}>{$metric[1]}</option>
                {/foreach}
            </select>
        </div>
    </div>
</div>

<!-- configure some piwik vars -->
<script type="text/javascript">

    window.__mapCssPath = "{$piwikUrl}plugins/UserCountryMap/css/map.css";
    window.__svgBasePath = "{$piwikUrl}plugins/UserCountryMap/svg/";

</script>

<!-- piwik-map.js -->
<script type="text/javascript">
{literal}

    $(function() {
        var map = $K.map('#UserCountryMap_map'),
            main = $('#UserCountryMap_container'),
            width = main.width();

        window.__userCountryMap = map;

        function renderCountryMap(iso) {
            map.loadMap(__svgBasePath + iso + '.svg', function() {

                var ratio, w, h;

                map.clear();

                ratio = map.viewAB.width / map.viewAB.height;
                w = map.container.width();
                h = w / ratio;
                map.container.height(h);
                map.resize(w, h);

                // add background
                map.addLayer({ id: 'context', key: 'iso' });
                map.addLayer({ id: "regions", className: "regionBG" });
                map.addLayer('regions');

                // add click events for surrounding countries
                map.onLayerEvent('click', function(path) {
                    renderCountryMap(path.iso);
                }, 'context');

                map.addSymbols({
                    data: map.getLayer('context').getPathsData(),
                    type: $K.Label,
                    filter: function(data) { return data.iso != iso },
                    location: function(data) { return 'context.'+data.iso; },
                    text: function(data) { return data.iso; },
                    'class': 'countryLabel'
                });

            }, { padding: -3});
        }

        map.loadStyles(__mapCssPath, function() {

            $('#UserCountryMap_content .loadingPiwik').hide();

            renderCountryMap('DEU');
            $('#userCountryMap-update').click(function() {
                renderCountryMap($('#userCountryMapInsertID').val());
            });
        });

    });

{/literal}
</script>
<!--<script type="text/javascript">
{literal}



    var fv = {};

    var params = {
        menu: "false",
        scale: "noScale",
        allowscriptaccess: "always",
        wmode: "opaque",
        bgcolor: "#FFFFFF",
        allowfullscreen: "true"

    };

{/literal}

    {* this hacks helps jquery to distingish between safari and chrome. *}
    var isSafari = (navigator.userAgent.toLowerCase().indexOf("safari") != -1 &&
            navigator.userAgent.toLowerCase().indexOf("chrome") == -1);

    fv.dataUrl = encodeURIComponent("{$dataUrl}");
    fv.hueMin = {$hueMin};
    fv.hueMax = {$hueMax};
    fv.satMin = {$satMin};
    fv.satMax = {$satMax};
    fv.lgtMin = {$lgtMin};
    fv.lgtMax = {$lgtMax};
    {* we need to add 22 pixel for safari due to wrong width calculation for the select *}
    fv.iconOffset = $('#userCountryMapSelectMetrics').width() + 22 + (isSafari ? 22 : 0);
    fv.defaultMetric = "{$defaultMetric}";

    fv.txtLoading = encodeURIComponent("{'General_Loading_js'|translate}");
    fv.txtLoadingData = encodeURIComponent("{'General_LoadingData'|translate}");
    fv.txtToggleFullscreen = encodeURIComponent("{'UserCountryMap_toggleFullscreen'|translate}");
    fv.txtExportImage = encodeURIComponent("{'General_ExportAsImage_js'|translate}");

{literal}

{/literal}

{literal}


    $("#userCountryMapSelectMetrics").change(function(el) {
        $("#UserCountryMap")[0].changeMode(el.currentTarget.value);
    });
    $("#userCountryMapSelectMetrics").keypress(function(e) {
        var keyCode = e.keyCode || e.which; 
        if (keyCode == 38 || keyCode == 40) { // if up or down key is pressed
            $(this).change(); // trigger the change event
        }
    });

    $(".userCountryMapFooterIcons a.tableIcon[var=fullscreen]").click(function() {
        $("#UserCountryMap")[0].setFullscreenMode();
    });

    $(".userCountryMapFooterIcons a.tableIcon[var=export_png]").click(function() {
        $("#UserCountryMap")[0].exportPNG();
    });

    $(window).resize(function() {
        if($('#UserCountryMap').length) {
            $("#UserCountryMap").height( Math.round($('#UserCountryMap').width() *.55) );
        }
    });
{/literal}
</script>-->

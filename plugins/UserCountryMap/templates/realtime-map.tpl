<div id="RealTimeMap" style="position:relative; overflow:hidden;">
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
    <style type="text/css">
{literal}

#RealTimeMap-black {
    position: absolute;
    right: 0;
    left: 0;
    z-index: 10001;
    width: 1000px;
    height: 1000px;
    background: #D5D3C8;
}

#RealTimeMap .loadingPiwik {
    position: absolute!important;
    top: 42%!important;
    right: 10px!important;
    left: 10px!important;
    z-index: 10002!important;
    display: block;
    color: #000;
    vertical-align: middle!important;
    text-align: center;
    text-shadow: 0 0 5px #fff;
}


.tableIcon.inactiveIcon {
    color: #99a;
}

.RealTimeMap-overlay,
.RealTimeMap-tooltip {
    display:block;
    position: absolute;
    z-index:1000;
}

.RealTimeMap-overlay .content,
.RealTimeMap-tooltip .content {
    padding:5px;
    border-radius:3px;
    background:rgba(255,255,255,0.9);
}

.RealTimeMap-title {
    top: 5px;
    left:5px;
}

.RealTimeMap-legend {
    right: 5px;
    font-size: 9px;
    bottom: 40px;
}
.RealTimeMap-info {
    left: 5px;
    font-size: 11px;
    bottom: 60px;
    max-width: 42%;
}
.RealTimeMap-info-btn {
    background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAAA3NCSVQICAjb4U/gAAAAOVBMVEX///8AAAAAAABXV1dSUlKsrKzExMTd3d3V1dXp6end3d3p6enz8/P7+/v39/f///+vqZ6oopWUjH2LPulWAAAAE3RSTlMAESIzM2Z3mZmqqrvd7u7/////UUgTXgAAAAlwSFlzAAALEgAACxIB0t1+/AAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNXG14zYAAAAYdEVYdENyZWF0aW9uIFRpbWUAMDMuMDEuMjAxM8rVeD8AAABnSURBVBiVhY/LFoAgCEQZ0p4W6f9/bIJ4slV3oTIeBoaICGADIAO8ibEwWn2IcwVovev7znqmCYRon9kEWUFvg3IysXyIXSil3fOvELupC9XUx7pQx/piDV1sVFLwMNF80sw97hj/AXRPCjtYdmhtAAAAAElFTkSuQmCC);
    width: 16px;
    height: 16px;
    cursor: pointer;
    left: 5px;
    bottom: 40px;
    position: absolute;
    z-index:1000;
    opacity: 0.9;

}
{/literal}
        </style>
    <div id="RealTimeMap_container">
        FOO!
        <div id="RealTimeMap_map" style="overflow:hidden"></div>

    </div>
    <div>
        <span class="loadingPiwik">
            <img src="{$piwikUrl}themes/default/images/loading-blue.gif"> {'General_LoadingData'|translate}...
        </span>
        <span class="noDataForReport" style="display:none">
            {'CoreHome_ThereIsNoDataForThisReport'|translate}...
        </span>
    </div>

</div>

<!-- configure some piwik vars -->
<script type="text/javascript">

{literal}
    var config = { metrics: {} };
{/literal}

    config.svgBasePath = "{$piwikUrl}plugins/UserCountryMap/svg/";
    config.liveRefreshAfterMs = {$liveRefreshAfterMs};

    RealTimeMap._ = JSON.parse('{$localeJSON}');
    RealTimeMap.reqParams = JSON.parse('{$reqParamsJSON}');

{literal}
    $(function() {
        //RealTimeMap.run(config)
    });
{/literal}

</script>

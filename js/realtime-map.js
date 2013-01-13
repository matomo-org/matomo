// # RealTimeMap

window.RealTimeMap = {};

RealTimeMap.run = function(config) {

    var map = $K.map('#RealTimeMap_map'),
        main = $('#RealTimeMap_container'),
        worldTotalVisits = 0,
        width = main.width(),
        scale = width / 300;

    window._liveMap = map;
    RealTimeMap.config = config;

    function _reportParams() {
        var params = $.extend(RealTimeMap.reqParams, {
            module: 'API',
            method: 'Live.getLastVisitsDetails',
            filter_limit: 20,
            showColumns: 'latitude,longitude,actions,lastActionTimestamp'
        });
        return params;
    }

    /*
     * updateMap is called by renderCountryMap() and renderWorldMap()
     */
    function _updateMap(svgUrl, callback) {
        map.loadMap(config.svgBasePath + svgUrl, function() {
            map.clear();
            onResize();
            callback();
            $('.ui-tooltip').remove(); // remove all existing tooltips
        }, { padding: -3});
    }

    /*
     * resizes the map to widget dimensions
     */
    function onResize() {
        var ratio, w, h;
        ratio = map.viewAB.width / map.viewAB.height;
        w = map.container.width();
        h = w / ratio;
        map.container.height(h-2);
        map.resize(w, h);

        if (w < 355) $('.tableIcon span').hide();
        else $('.tableIcon span').show();
    }


    _updateMap('world.svg', function() {
        $('#widgetRealTimeMapliveMap .loadingPiwik, #RealTimeMap .loadingPiwik').hide();

        map.addLayer('countries', {
            styles: {
                fill: '#aa9',
                stroke: '#ffffff',
                'stroke-width': 0.2
            }
        });

        $.ajax({
            url: 'index.php',
            type: 'POST',
            data: _reportParams()
        }).done(function(report) {

            var newest = report[0],
                oldest = report[report.length-1];

            function age(r) {
                var o = (r.lastActionTimestamp - oldest.lastActionTimestamp) / (newest.lastActionTimestamp - oldest.lastActionTimestamp);
                return o;
            }

            report = report.reverse();

            map.addSymbols({
                data: report,
                type: $K.Bubble,
                radius: function(r) { return 3 * scale * Math.pow(age(r),2) + 2; },
                location: function(r) { return [r.longitude, r.latitude]; },
                attrs: function(r) {
                    return {
                        fill: chroma.hsl(30.25, age(r), 0.45 - (1-age(r))*0.3),
                        'fill-opacity': Math.pow(age(r),2),
                        'stroke-opacity': Math.pow(age(r),1.7),
                        stroke: '#fff',
                        'stroke-width': age(r)
                    };
                },
                click: function(r, s) {
                    var c = map.paper.circle().attr(s.path.attrs);
                    c.insertBefore(s.path);
                    c.attr({ fill: false });
                    c.animate({ r: c.attrs.r*3, 'stroke-width': 5, opacity: 0 }, 1500,
                        'linear', function() { c.remove(); });
                    var col = s.path.attrs.fill, rad = s.path.attrs.r;
                    s.path.attr({ fill: '#fdb', r: 0.1 });
                    s.path.animate({ fill: col, r: rad }, 700, 'bounce');
                }
            });
        });
    });
};

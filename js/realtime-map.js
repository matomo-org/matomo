// # RealTimeMap

window.RealTimeMap = {};

var VisitorBubble = function() {};
$.extend(VisitorBubble.prototype, Kartograph.Bubble.prototype);

RealTimeMap.run = function(config) {

    var map = $K.map('#RealTimeMap_map'),
        main = $('#RealTimeMap_container'),
        worldTotalVisits = 0,
        maxVisits = 100,
        width = main.width(),
        scale = width / 300,
        lastTimestamp = -1,
        lastVisits = [];

    window._liveMap = map;
    RealTimeMap.config = config;

    function _reportParams() {
        var params = $.extend(RealTimeMap.reqParams, {
            module: 'API',
            method: 'Live.getLastVisitsDetails',
            filter_limit: maxVisits,
            showColumns: 'latitude,longitude,actions,lastActionTimestamp',
            minTimestamp: lastTimestamp,
            date: 'today'
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


    function refreshVisits() {
        $.ajax({
            url: 'index.php',
            type: 'POST',
            data: _reportParams()
        }).done(function(report) {

            lastVisits = [].concat(report).concat(lastVisits).slice(0, maxVisits);

            var newest = new Date().getTime() / 1000,
                oldest = lastVisits[lastVisits.length-1].lastActionTimestamp;

            lastTimestamp = newest.lastActionTimestamp;

            function age(r) {
                var o = (r.lastActionTimestamp - oldest) / (newest - oldest);
                return o;
            }

            try {
                map.removeSymbols();
            } catch (e) {}

            map.addSymbols({
                data: lastVisits,
                type: VisitorBubble,
                sortBy: function(r) { return r.lastActionTimestamp; },
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
                tooltip: function(r) {
                    return '<h3>'+city.city_name+'</h3>'+
                        formatValueForTooltips(city, metric, iso);
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

        var lastVisitId = -1,
            lastReport = [];

        refreshVisits();
        setInterval(refreshVisits, config.liveRefreshAfterMs);
    });
};

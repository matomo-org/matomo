// # RealTimeMap

window.RealTimeMap = {};

/*
 * main function, called from realtime-map.tpl
 */
RealTimeMap.run = function(config) {

    var debug = 0;

    var map = window.__realtimemap = $K.map('#RealTimeMap_map'),
        main = $('#RealTimeMap_container'),
        worldTotalVisits = 0,
        maxVisits = 100,
        width = main.width(),
        radScale = Math.sqrt($('#RealTimeMap_map').width() / 400),
        minRad = 4 * radScale,
        maxRad = 10 * radScale,
        lastTimestamp = -1,
        lastVisits = [],
        visitSymbols,
        tokenAuth = ''+RealTimeMap.reqParams.token_auth,
        oldest,
        isFullscreenWidget = $('.widget').parent().get(0) == document.body,
        now,
        nextReqTimer,
        symbolFadeInTimer = [],
        colorMode = 'default',
        currentMap = 'world',

        currentTheme = 'white',
        colorTheme = {
            white: {
                bg: '#fff',
                fill: '#aa9'
            },
            black: {
                bg: '#000',
                fill: '#444440'
            }
        };

    RealTimeMap.widget = $('#widgetRealTimeMaprealtimeMap').parent();

    window._liveMap = map;
    RealTimeMap.config = config;

    var preset = RealTimeMap.widget.dashboardWidget('getWidgetObject').parameters;
    if (preset) {
        currentTheme = preset.colorTheme;
        colorMode = preset.colorMode;
        currentMap = preset.lastMap;
    }

    /*
     * returns the parameters for API calls, extended from
     * RealTimeMap.reqParams which is set in template
     */
    function _reportParams(firstRun) {
        var params = $.extend(RealTimeMap.reqParams, {
            module: 'API',
            method: 'Live.getLastVisitsDetails',
            filter_limit: maxVisits,
            showColumns: ['latitude','longitude','actions','lastActionTimestamp',
                'visitLocalTime','city','country','referrerType','referrerName',
                'referrerTypeName','browserIcon','operatingSystemIcon',
                'countryFlag','idVisit','actionDetails','continentCode'].join(','),
            minTimestamp: firstRun ? -1 : lastTimestamp,
            date: 'today'
        });
        return params;
    }

    /*
     * wrapper around jQuery.ajax, moves token_auth parameter
     * to POST data while keeping other parameters as GET
     */
    function ajax(params) {
        delete params['token_auth'];
        return $.ajax({
            url: 'index.php?' + $.param(params),
            dataType: 'json',
            data: { token_auth: tokenAuth },
            type: 'POST'
        });
    }

    /*
     * updateMap is called by renderCountryMap() and renderWorldMap()
     */
    function _updateMap(svgUrl, callback) {
        if (svgUrl === undefined) return;
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
        radScale = Math.sqrt(w / 400);
        maxRad = 10 * radScale;
        minRad = 4 * radScale;

        h = Math.min(w / ratio, $(window).height()-30);
        map.container.height(h-2);
        map.resize(w, h);

        if (w < 355) $('.tableIcon span').hide();
        else $('.tableIcon span').show();
    }

    /*
     * to ensure that onResize is not called a hundred times
     * while resizing the browser window, this functions
     * makes sure to only call onResize at the end
     */
    function onResizeLazy() {
        clearTimeout(RealTimeMap._resizeTimer);
        RealTimeMap._resizeTimer = setTimeout(onResize, 300);
    }

    /*
     * returns value between 0..1, where 1 means that the
     * visit is fresh, and 0 means the visit is almost gone
     * from the map
     */
    function age(r) {
        var now = new Date().getTime() / 1000;
        var o = (r.lastActionTimestamp - oldest) / (now - oldest);
        return Math.min(1, Math.max(0, o));
    }

    function relativeTime(ds) {
        var val = function(val) { return '<b>'+Math.round(val)+'</b>'; };
        return (ds < 90 ? RealTimeMap._.seconds_ago.replace('%s', val(ds))
            : ds < 5400 ? RealTimeMap._.minutes_ago.replace('%s', val(ds/60))
            : ds < 129600 ? RealTimeMap._.hours_ago.replace('%s', val(ds/3600))
            : RealTimeMap._.days_ago.replace('%s', val(ds/86400)));
    }

    /*
     * returns the content of the tooltip displayed for each
     * visitor on the map
     */
    function visitTooltip(r) {
        var ds = new Date().getTime() / 1000 - r.lastActionTimestamp,
            ad = r.actionDetails,
            ico = function(src) { return '<img src="'+src+'" alt="" class="icon" />&nbsp;'; };
        return '<h3>'+r.city+' / '+r.country+'</h3>'+
            // icons
            ico(r.countryFlag)+ico(r.browserIcon)+ico(r.operatingSystemIcon)+'<br/>'+
            // last action
            (ad && ad.length && ad[ad.length-1].pageTitle ? '<em>' + ad[ad.length-1].pageTitle+'</em><br/>' : '')+
            // time of visit
            '<div class="rel-time" data-actiontime="'+r.lastActionTimestamp+'">'+relativeTime(ds)+'</div>'+
            // either from or direct
            (r.referrerType == "direct" ? r.referrerTypeName :
            RealTimeMap._.from + ': '+r.referrerName) + '<br />' +
            // local time
            '<small>'+RealTimeMap._.local_time+': '+r.visitLocalTime+'</small>';
    }

    /*
     * the radius of the symbol depends on the lastActionTimestamp
     */
    function visitRadius(r) {
        return Math.pow(age(r),4) * (maxRad - minRad) + minRad;
    }

    /*
     * defines the color of the map symbols.
     * depends on colorMode, which is set to 'default'
     * unless you type Shift+Alt+C
     */
    function visitColor(r) {
        var col;
        if (colorMode == 'referrerType') {
            col = ({
                website: '#F29007',
                direct: '#5170AE',
                search: '#CC3399'
            })[r.referrerType];
        }
        // defu
        else col = chroma.hsl(42 * age(r), Math.sqrt(age(r)), 0.50 - (1-age(r))*0.45);
        return col;
    }

    /*
     * attributes of the map symbols
     */
    function visitSymbolAttrs(r) {
        return {
            fill: visitColor(r),
            'fill-opacity': Math.pow(age(r),2) * 0.8 + 0.2,
            'stroke-opacity': Math.pow(age(r),1.7) * 0.8 + 0.2,
            stroke: '#fff',
            'stroke-width': age(r),
            r: visitRadius(r)
        };
    }

    /*
     * eventually highlights the row in LiveVisitors widget
     * that corresponds to a visit on the map
     */
    function highlightVisit(r) {
        $('#visitsLive li#'+r.idVisit + ' .datetime')
            .css('background', '#E4CD74');
    }

    /*
     * removes the highlight after the mouse left
     * the visit marker on the map
     */
    function unhighlightVisit(r) {
        $('#visitsLive li#'+r.idVisit + ' .datetime')
            .css({ background: '#E4E2D7' });
    }

    /*
     * create a nice popping animation for appearing
     * visit symbols.
     */
    function animateSymbol(s) {
        // create a white outline and explode it
        var c = map.paper.circle().attr(s.path.attrs);
        c.insertBefore(s.path);
        c.attr({ fill: false });
        c.animate({ r: c.attrs.r*3, 'stroke-width': 7, opacity: 0 }, 2500,
            'linear', function() { c.remove(); });
        // ..and pop the bubble itself
        var col = s.path.attrs.fill,
            rad = s.path.attrs.r;
        s.path.show();
        s.path.attr({ fill: '#fdb', r: 0.1, opacity: 1 });
        s.path.animate({ fill: col, r: rad }, 700, 'bounce');

    }

    /*
     * this function requests new data from Live.getLastVisitsDetails
     * and updates the symbols on the map. Then, it sets a timeout
     * to call itself after the refresh time set by Piwik
     *
     * If firstRun is true, the SymbolGroup is initialized
     */
    function refreshVisits(firstRun) {
        /*
         * this is called after new visit reports came in
         */
        function gotNewReport(report) {
            // successful request, so set timeout for next API call
            nextReqTimer = setTimeout(refreshVisits, config.liveRefreshAfterMs);

            // hide loading indicator
            $('.realTimeMap_overlay img').hide();

            // store current timestamp
            now = new Date().getTime() / 1000;

            if (firstRun) {  // if we run this the first time, we initialiize the map symbols
                visitSymbols = map.addSymbols({
                    data: [],
                    type: Kartograph.Bubble,
                    sortBy: function(r) { return r.lastActionTimestamp; },
                    radius: visitRadius,
                    location: function(r) { return [r.longitude, r.latitude]; },
                    attrs: visitSymbolAttrs,
                    tooltip: visitTooltip,
                    mouseenter: highlightVisit,
                    mouseleave: unhighlightVisit,
                    click: function(r, s, evt) {
                        evt.stopPropagation();
                        var cont = cont[s.data.continentCode];
                        if (cont && cont != currentMap) {
                            updateMap(cont);
                        }
                    }
                });

                // clear existing report
                lastVisits = [];
            }

            if (report.length) {

                // filter results without location
                report = report.filter(function(r) {
                    return r.latitude !== null;
                });

                // check wether we got any geolocated visits left
                if (!report.length) {
                    $('.realTimeMap_overlay').hide();
                    $('#RealTimeMap_meta .noDataForReport').show();
                    return;
                } else {
                    $('#RealTimeMap_meta .noDataForReport').hide();
                    $('.realTimeMap_overlay').show();
                }

                lastVisits = [].concat(report).concat(lastVisits).slice(0, maxVisits);
                oldest = lastVisits[lastVisits.length-1].lastActionTimestamp;

                // let's try a different strategy
                // remove symbols that are too old
                //console.info('before', $('circle').length, visitSymbols.symbols.length);
                var _removed = 0;
                visitSymbols.remove(function(r) {
                    if (r.lastActionTimestamp < oldest) _removed++;
                    return r.lastActionTimestamp < oldest;
                });

                // update symbols that remain
                visitSymbols.update({
                    radius: function(d) { return visitSymbolAttrs(d).r; },
                    attrs: visitSymbolAttrs
                }, true);

                // add new symbols
                var newSymbols = [];
                $.each(report, function(i, r) {
                    newSymbols.push(visitSymbols.add(r));
                });

                //console.info('added', newSymbols.length, visitSymbols.symbols.length, $('circle').length);
                visitSymbols.layout().render();

                //console.info('rendered', visitSymbols.symbols.length, $('circle').length);

                $.each(newSymbols, function(i, s) {
                    if (i>10) return false;
                    //if (s.data.lastActionTimestamp > lastTimestamp) {
                    s.path.hide(); // hide new symbol at first
                    var t = setTimeout(function() { animateSymbol(s); },
                        1000 * (s.data.lastActionTimestamp - now) + config.liveRefreshAfterMs);
                    symbolFadeInTimer.push(t);
                    //}
                });

                lastTimestamp = report[0].lastActionTimestamp;

                // show
                var dur = lastTimestamp - oldest, d;
                if (dur < 60) d = dur+ ' ' + RealTimeMap._.seconds;
                else if (dur < 3600) d = Math.ceil(dur / 60) + ' ' + RealTimeMap._.minutes;
                else d = Math.ceil(dur / 3600) + ' ' + RealTimeMap._.hours;
                $('.realTimeMap_timeSpan').html(d);

            }
            firstRun = false;
        }

        if (firstRun && lastVisits.length) {
            // zoom changed, use cached report data
            gotNewReport(lastVisits.slice());
        } else {
            // request API for new data
            $('.realTimeMap_overlay img').show();
            ajax(_reportParams(firstRun)).done(gotNewReport);
        }
    }

    /*
     * Set up the base map after loading of the SVG. Adds a single layer
     * that shows countries in gray with white outlines. Also this is where
     * the zoom behaviour is initialized.
     */
    function initMap() {
        $('#widgetRealTimeMapliveMap .loadingPiwik, #RealTimeMap .loadingPiwik').hide();
        map.addLayer('countries', {
            styles: {
                fill: colorTheme[currentTheme].fill,
                stroke: colorTheme[currentTheme].bg,
                'stroke-width': 0.2
            },
            click: function(d, p, evt) {
                evt.stopPropagation();
                if (currentMap != 'world') {  // zoom out if zoomed in
                    updateMap('world');
                } else {  // or zoom to continent view otherwise
                    updateMap(UserCountryMap.ISO3toCONT[d.iso]);
                }
            },
            title: function(d) {
                // return the country name for educational purpose
                return d.name;
            }
        });

        var lastVisitId = -1,
            lastReport = [];
        refreshVisits(true);
    }

    function storeSettings() {
        RealTimeMap.widget.dashboardWidget('setParameters', {
            lastMap: currentMap, theme: colorTheme, colorMode: colorMode
        });
    }

    /*
     * updates the map view (after changing the zoom)
     * clears all existing timeouts
     */
    function updateMap(_map) {
        clearTimeout(nextReqTimer);
        $.each(symbolFadeInTimer, function(i, t) {
            clearTimeout(t);
        });
        symbolFadeInTimer = [];
        try {
            map.removeSymbols();
        } catch (e) {}
        currentMap = _map;
        _updateMap(currentMap + '.svg', initMap);
        storeSettings();
    }

    updateMap(location.hash && (location.hash == '#world' || location.hash.match(/^#[A-Z][A-Z]$/)) ? location.hash.substr(1) : 'world'); // TODO: restore last state

    // clicking on map background zooms out
    $('#RealTimeMap_map').off('click').click(function() {
        if (currentMap != 'world') updateMap('world');
    });

    // secret gimmick shortcuts
    $(window).keydown(function(evt) {
        // shift+alt+C changes color mode
        if (evt.shiftKey && evt.altKey && evt.keyCode == 67) {
            colorMode = ({
                'default': 'referrerType',
                referrerType: 'default'
            })[colorMode];
            storeSettings();
        }

        function switchTheme() {
            $('#RealTimeMap').css({ background: colorTheme[currentTheme].bg });
            if (isFullscreenWidget) {
                $('body').css({ background: colorTheme[currentTheme].bg });
                $('.widget').css({ 'border-width': 1 });
            }
            map.getLayer('countries')
                .style('fill', colorTheme[currentTheme].fill )
                .style('stroke', colorTheme[currentTheme].bg );

            storeSettings();
        }

        // shift+alt+B: switch to black background
        if (evt.shiftKey && evt.altKey && evt.keyCode == 66) {
            currentTheme = 'black';
            switchTheme();
        }

        // shift+alt+W: return to white background
        if (evt.shiftKey && evt.altKey && evt.keyCode == 87) {
            currentTheme = 'white';
            switchTheme();
        }

    }); // */
    // make sure the map adapts to the widget size
    $(window).resize(onResizeLazy);

    // setup automatic tooltip updates
    setInterval(function() {
        $('.qtip .rel-time').each(function(i, el) {
            el = $(el);
            var ds = new Date().getTime() / 1000 - el.data('actiontime');
            el.html(relativeTime(ds));
        });
    }, 1000);
};

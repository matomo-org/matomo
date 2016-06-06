/*!
 * Piwik - free/libre analytics platform
 *
 * Real time visitors map
 * Using Kartograph.js http://kartograph.org/
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {

    var UIControl = require('piwik/UI').UIControl;

    var RealtimeMap = window.UserCountryMap.RealtimeMap = function (element) {
        UIControl.call(this, element);
        this._init();
        this.run();
    };

    RealtimeMap.initElements = function () {
        UIControl.initElements(this, '.RealTimeMap');
    };

    $.extend(RealtimeMap.prototype, UIControl.prototype, {

        _init: function () {
            var $element = this.$element;

            this.config = JSON.parse($element.attr('data-config'));

            // If the map is loaded from the menu, do a few tweaks to clean up the display
            if ($element.attr('data-standalone') == 1) {
                this._initStandaloneMap();
            }

            // handle widgetry
            if ($('#dashboardWidgetsArea').length) {
                var $widgetContent = $element.closest('.widgetContent');

                var self = this;
                $widgetContent.on('widget:maximise', function () {
                    self.resize();
                }).on('widget:minimise', function () {
                    self.resize();
                });
            }

            // set unique ID for kartograph map div
            this.uniqueId = 'RealTimeMap_map-' + this._controlId;
            $('.RealTimeMap_map', $element).attr('id', this.uniqueId);

            // create the map
            this.map = $K.map('#' + this.uniqueId);

            $element.focus();
        },

        _initStandaloneMap: function () {
            $('#periodString').hide();
            initTopControls();
            $('#secondNavBar').on('piwikSwitchPage', function (event, item) {
                var href = $(item).attr('href');
                var clickedMenuIsNotMap = !href || (href.indexOf('module=UserCountryMap&action=realtimeWorldMap') == -1);
                if (clickedMenuIsNotMap) {
                    $('#periodString').show();
                    initTopControls();
                }
            });
            $('.realTimeMap_overlay').css('top', '0px');
            $('.realTimeMap_datetime').css('top', '20px');
        },

        run: function () {
            var debug = 0;

            var self = this,
                config = self.config,
                _ = config._,
                map = self.map,
                main = $('.RealTimeMap_container', this.$element),
                worldTotalVisits = 0,
                maxVisits = config.maxVisits || 100,
                changeVisitAlpha = typeof config.changeVisitAlpha === 'undefined' ? true : config.changeVisitAlpha,
                removeOldVisits = typeof config.removeOldVisits === 'undefined' ? true : config.removeOldVisits,
                doNotRefreshVisits = typeof config.doNotRefreshVisits === 'undefined' ? false : config.doNotRefreshVisits,
                enableAnimation = typeof config.enableAnimation === 'undefined' ? true : config.enableAnimation,
                forceNowValue = typeof config.forceNowValue === 'undefined' ? false : +config.forceNowValue,
                width = main.width(),
                lastTimestamp = -1,
                lastVisits = [],
                visitSymbols,
                tokenAuth = '' + config.reqParams.token_auth,
                oldest,
                isFullscreenWidget = $('.widget').parent().get(0) == document.body,
                now,
                nextReqTimer,
                symbolFadeInTimer = [],
                colorMode = 'default',
                currentMap = 'world',
                yesterday = false,
                colorManager = piwik.ColorManager,
                colors = colorManager.getColors('realtime-map', ['white-bg', 'white-fill', 'black-bg', 'black-fill', 'visit-stroke',
                                                                 'website-referrer-color', 'direct-referrer-color', 'search-referrer-color',
                                                                 'live-widget-highlight', 'live-widget-unhighlight', 'symbol-animate-fill', 'region-stroke-color']),
                currentTheme = 'white',
                colorTheme = {
                    white: {
                        bg: colors['white-bg'],
                        fill: colors['white-fill']
                    },
                    black: {
                        bg: colors['black-bg'],
                        fill: colors['black-fill']
                    }
                },
                visitStrokeColor = colors['visit-stroke'],
                referrerColorWebsite = colors['referrer-color-website'],
                referrerColorDirect = colors['referrer-color-direct'],
                referrerColorSearch = colors['referrer-color-search'],
                liveWidgetHighlightColor = colors['live-widget-highlight'],
                liveWidgetUnhighlightColor = colors['live-widget-unhighlight'],
                symbolAnimateFill = colors['symbol-animate-fill']
                ;

            self.widget = $('#widgetRealTimeMaprealtimeMap').parent();

            var preset = self.widget.dashboardWidget('getWidgetObject').parameters;
            if (preset) {
                currentTheme = preset.colorTheme;
                colorMode = preset.colorMode;
                currentMap = preset.lastMap;
            }

            /*
             * returns the parameters for API calls, extended from
             * self.reqParams which is set in template
             */
            function _reportParams(firstRun) {
                return $.extend(config.reqParams, {
                    module: 'API',
                    method: 'Live.getLastVisitsDetails',
                    filter_limit: maxVisits,
                    showColumns: ['latitude', 'longitude', 'actions', 'lastActionTimestamp',
                        'visitLocalTime', 'city', 'country', 'referrerType', 'referrerName',
                        'referrerTypeName', 'browserIcon', 'operatingSystemIcon',
                        'countryFlag', 'idVisit', 'actionDetails', 'continentCode',
                        'actions', 'searches', 'goalConversions', 'visitorId', 'userId'].join(','),
                    minTimestamp: firstRun ? -1 : lastTimestamp
                });
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
                map.loadMap(config.svgBasePath + svgUrl, function () {
                    map.clear();
                    self.resize();
                    callback();
                    $('.ui-tooltip').remove(); // remove all existing tooltips
                }, { padding: -3});
            }

            /*
             * to ensure that onResize is not called a hundred times
             * while resizing the browser window, this functions
             * makes sure to only call onResize at the end
             */
            function onResizeLazy() {
                clearTimeout(self._resizeTimer);
                self._resizeTimer = setTimeout(self.resize.bind(self), 300);
            }

            /*
             * returns value betwddn 0..1, where 1 means that the
             * visit is fresh, and 0 means the visit is almost gone
             * from the map
             */
            function age(r) {
                var nowSecs = Math.floor(now);
                var o = (r.lastActionTimestamp - oldest) / (nowSecs - oldest);
                return Math.min(1, Math.max(0, o));
            }

            function relativeTime(ds) {
                var val = function (val) { return '<strong>' + Math.round(val) + '</strong>'; };
                return (ds < 90 ? _.seconds_ago.replace('%s', val(ds))
                    : ds < 5400 ? _.minutes_ago.replace('%s', val(ds / 60))
                    : ds < 129600 ? _.hours_ago.replace('%s', val(ds / 3600))
                    : _.days_ago.replace('%s', val(ds / 86400)));
            }

            /*
             * returns the content of the tooltip displayed for each
             * visitor on the map
             */
            function visitTooltip(r) {
                var ds = new Date().getTime() / 1000 - r.lastActionTimestamp,
                    ad = r.actionDetails,
                    ico = function (src) { return '<img src="' + src + '" alt="" class="icon" />&nbsp;'; };
                return '<h3>' + (r.city ? r.city + ' / ' : '') + r.country + '</h3>' +
                    // icons
                    ico(r.countryFlag) + ico(r.browserIcon) + ico(r.operatingSystemIcon) + '<br/>' +
                    // User ID
                    (r.userId ? _pk_translate('General_UserId') + ':&nbsp;' + r.userId + '<br/>' : '') +
                    // last action
                    (ad && ad.length && ad[ad.length - 1].pageTitle ? '<em>' + ad[ad.length - 1].pageTitle + '</em><br/>' : '') +
                    // time of visit
                    '<div class="rel-time" data-actiontime="' + r.lastActionTimestamp + '">' + relativeTime(ds) + '</div>' +
                    // either from or direct
                    (r.referrerType == "direct" ? r.referrerTypeName :
                        _.from + ': ' + r.referrerName) + '<br />' +
                    // local time
                    '<small>' + _.local_time + ': ' + r.visitLocalTime + '</small><br />' +
                    // goals, if available
                    (self.config.siteHasGoals && r.goalConversions ? '<small>' + _.goal_conversions.replace('%s', '<strong>' + r.goalConversions + '</strong>') +
                        (r.searches > 0 ? ', ' + _.searches.replace('%s', r.searches) : '') + '</small><br />' : '') +
                    // actions and searches
                    '<small>' + _.actions.replace('%s', '<strong>' + r.actions + '</strong>') +
                    (r.searches > 0 ? ', ' + _.searches.replace('%s', '<strong>' + r.searches + '</strong>') : '') + '</small>';
            }

            /*
             * the radius of the symbol depends on the lastActionTimestamp
             */
            function visitRadius(r) {
                return Math.pow(age(r), 4) * (self.maxRad - self.minRad) + self.minRad;
            }

            /*
             * defines the color of the map symbols.
             * depends on colorMode, which is set to 'default'
             * unless you type Shift+Alt+C
             */
            function visitColor(r) {
                var col,
                    engaged = self.config.siteHasGoals ? r.goalConversions > 0 : r.actions > 4;
                if (colorMode == 'referrerType') {
                    col = ({
                        website: referrerColorWebsite,
                        direct: referrerColorDirect,
                        search: referrerColorSearch
                    })[r.referrerType];
                }
                // defu
                else col = chroma.hsl(
                    42 * age(r), // hue
                    Math.sqrt(age(r)), // saturation
                    (engaged ? 0.65 : 0.5) - (1 - age(r)) * 0.45  // lightness
                );
                return col;
            }

            /*
             * attributes of the map symbols
             */
            function visitSymbolAttrs(r) {
                var result = {
                    fill: visitColor(r).hex(),
                    stroke: visitStrokeColor,
                    'stroke-width': 1 * age(r),
                    r: visitRadius(r),
                    cursor: 'pointer'
                };
                if (changeVisitAlpha) {
                    result['fill-opacity'] = Math.pow(age(r), 2) * 0.8 + 0.2;
                    result['stroke-opacity'] = Math.pow(age(r), 1.7) * 0.8 + 0.2;
                }
                return result;
            }

            /*
             * eventually highlights the row in LiveVisitors widget
             * that corresponds to a visit on the map
             */
            function highlightVisit(r) {
                $('#visitsLive').find('li#' + r.idVisit + ' .datetime')
                    .css('background', liveWidgetHighlightColor);
            }

            /*
             * removes the highlight after the mouse left
             * the visit marker on the map
             */
            function unhighlightVisit(r) {
                $('#visitsLive').find('li#' + r.idVisit + ' .datetime')
                    .css({ background: liveWidgetUnhighlightColor });
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
                c.animate({ r: c.attrs.r * 3, 'stroke-width': 7, opacity: 0 }, 2500,
                    'linear', function () { c.remove(); });
                // ..and pop the bubble itself
                var col = s.path.attrs.fill,
                    rad = s.path.attrs.r;
                s.path.show();
                s.path.attr({ fill: symbolAnimateFill, r: 0.1, opacity: 1 });
                s.path.animate({ fill: col, r: rad }, 700, 'bounce');

            }

            // default click behavior. if a visit is clicked, the visitor profile is launched,
            // otherwise zoom in or out.
            // TODO: visitor profile launching logic should probably be contained in
            //       visitorProfile.js. not sure how to do that, though...
            this.$element.on('mapClick', function (e, visit, mapPath) {
                var VisitorProfileControl = require('piwik/UI').VisitorProfileControl;
                if (visit
                    && VisitorProfileControl
                    && !self.$element.closest('.visitor-profile').length
                ) {
                    VisitorProfileControl.showPopover(visit.visitorId);
                } else {
                    var cont = UserCountryMap.cont2cont[mapPath.data.continentCode];
                    if (cont && cont != currentMap) {
                        updateMap(cont);
                    }
                }
            });

            /*
             * this function requests new data from Live.getLastVisitsDetails
             * and updates the symbols on the map. Then, it sets a timeout
             * to call itself after the refresh time set by Piwik
             *
             * If firstRun is true, the SymbolGroup is initialized
             */
            function refreshVisits(firstRun) {
                if (lastTimestamp != -1
                    && doNotRefreshVisits
                    && !firstRun
                ) {
                    return;
                }

                /*
                 * this is called after new visit reports came in
                 */
                function gotNewReport(report) {
                    // if the map has been destroyed, do nothing
                    if (!self.map || !self.$element.length || !$.contains(document, self.$element[0])) {
                        return;
                    }

                    // successful request, so set timeout for next API call
                    nextReqTimer = setTimeout(refreshVisits, config.liveRefreshAfterMs);

                    // hide loading indicator
                    $('.realTimeMap_overlay img').hide();
                    $('.realTimeMap_overlay .loading_data').hide();

                    // store current timestamp
                    now = forceNowValue || (new Date().getTime() / 1000);

                    if (firstRun) {  // if we run this the first time, we initialiize the map symbols
                        visitSymbols = map.addSymbols({
                            data: [],
                            type: $K.Bubble,
                            /*title: function(d) {
                             return visitRadius(d) > 15 && d.actions > 1 ? d.actions : '';
                             },
                             labelattrs: {
                             fill: '#fff',
                             'font-weight': 'bold',
                             'font-size': 11,
                             stroke: false,
                             cursor: 'pointer'
                             },*/
                            sortBy: function (r) { return r.lastActionTimestamp; },
                            radius: visitRadius,
                            location: function (r) { return [r.longitude, r.latitude]; },
                            attrs: visitSymbolAttrs,
                            tooltip: visitTooltip,
                            mouseenter: highlightVisit,
                            mouseleave: unhighlightVisit,
                            click: function (visit, mapPath, evt) {
                                evt.stopPropagation();
                                self.$element.trigger('mapClick', [visit, mapPath]);
                            }
                        });

                        // clear existing report
                        lastVisits = [];
                    }

                    if (report.length) {
                        // filter results without location
                        report = $.grep(report, function (r) {
                            return r.latitude !== null;
                        });
                    }

                    // check wether we got any geolocated visits left
                    if (!report.length) {
                        $('.realTimeMap_overlay .showing_visits_of').hide();
                        $('.realTimeMap_overlay .no_data').show();
                        return;
                    } else {
                        $('.realTimeMap_overlay .showing_visits_of').show();
                        $('.realTimeMap_overlay .no_data').hide();

                        if (yesterday === false) {
                            yesterday = report[0].lastActionTimestamp - 24 * 60 * 60;
                        }

                        lastVisits = [].concat(report).concat(lastVisits).slice(0, maxVisits);
                        oldest = Math.max(lastVisits[lastVisits.length - 1].lastActionTimestamp, yesterday);

                        // let's try a different strategy
                        // remove symbols that are too old
                        var _removed = 0;
                        if (removeOldVisits) {
                            visitSymbols.remove(function (r) {
                                if (r.lastActionTimestamp < oldest) _removed++;
                                return r.lastActionTimestamp < oldest;
                            });
                        }

                        // update symbols that remain
                        visitSymbols.update({
                            radius: function (d) { return visitSymbolAttrs(d).r; },
                            attrs: visitSymbolAttrs
                        }, true);

                        // add new symbols
                        var newSymbols = [];
                        $.each(report, function (i, r) {
                            newSymbols.push(visitSymbols.add(r));
                        });

                        visitSymbols.layout().render();

                        if (enableAnimation) {
                            $.each(newSymbols, function (i, s) {
                                if (i > 10) return false;

                                s.path.hide(); // hide new symbol at first
                                var t = setTimeout(function () { animateSymbol(s); },
                                    1000 * (s.data.lastActionTimestamp - now) + config.liveRefreshAfterMs);
                                symbolFadeInTimer.push(t);
                            });
                        }

                        lastTimestamp = report[0].lastActionTimestamp;

                        // show
                        var dur = lastTimestamp - oldest, d;
                        if (dur < 60) d = dur + ' ' + _.seconds;
                        else if (dur < 3600) d = Math.ceil(dur / 60) + ' ' + _.minutes;
                        else d = Math.ceil(dur / 3600) + ' ' + _.hours;
                        $('.realTimeMap_timeSpan').html(d);

                    }
                    firstRun = false;
                }

                if (firstRun && lastVisits.length) {
                    // zoom changed, use cached report data
                    gotNewReport(lastVisits.slice());
                } else if (Visibility.hidden()) {
                    nextReqTimer = setTimeout(refreshVisits, config.liveRefreshAfterMs);
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
                $('#widgetRealTimeMapliveMap .loadingPiwik, .RealTimeMap .loadingPiwik').hide();
                map.addLayer(currentMap.length == 3 ? 'context' : 'countries', {
                    styles: {
                        fill: colorTheme[currentTheme].fill,
                        stroke: colorTheme[currentTheme].bg,
                        'stroke-width': 0.2
                    },
                    click: function (d, p, evt) {
                        evt.stopPropagation();
                        if (currentMap.length == 2){   // zoom to country
                            updateMap(d.iso);
                        } else if (currentMap != 'world') {  // zoom out if zoomed in
                            updateMap('world');
                        } else {  // or zoom to continent view otherwise
                            updateMap(UserCountryMap.ISO3toCONT[d.iso]);
                        }
                    },
                    title: function (d) {
                        // return the country name for educational purpose
                        return d.name;
                    }
                });
                if (currentMap.length == 3){
                    map.addLayer('regions', {
                        styles: {
                            stroke: colors['region-stroke-color']
                        }
                    });
                }
                var lastVisitId = -1,
                    lastReport = [];
                refreshVisits(true);
            }

            function storeSettings() {
                self.widget.dashboardWidget('setParameters', {
                    lastMap: currentMap, theme: colorTheme, colorMode: colorMode
                });
            }

            /*
             * updates the map view (after changing the zoom)
             * clears all existing timeouts
             */
            function updateMap(_map) {
                clearTimeout(nextReqTimer);
                $.each(symbolFadeInTimer, function (i, t) {
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

            updateMap(location.hash && (location.hash == '#world' || location.hash.match(/^#[A-Z]{2,3}$/)) ? location.hash.substr(1) : 'world'); // TODO: restore last state

            // clicking on map background zooms out
            $('.RealTimeMap_map', this.$element).off('click').click(function () {
                if (currentMap != 'world') updateMap('world');
            });

            // secret gimmick shortcuts
            this.$element.on('keydown', function (evt) {
                // shift+alt+C changes color mode
                if (evt.shiftKey && evt.altKey && evt.keyCode == 67) {
                    colorMode = ({
                        'default': 'referrerType',
                        referrerType: 'default'
                    })[colorMode];
                    storeSettings();
                }

                function switchTheme() {
                    self.$element.css({ background: colorTheme[currentTheme].bg });
                    if (isFullscreenWidget) {
                        $('body').css({ background: colorTheme[currentTheme].bg });
                        $('.widget').css({ 'border-width': 1 });
                    }
                    map.getLayer('countries')
                        .style('fill', colorTheme[currentTheme].fill)
                        .style('stroke', colorTheme[currentTheme].bg);

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
            });

            // make sure the map adapts to the widget size
            $(window).on('resize.' + this.uniqueId, onResizeLazy);

            // setup automatic tooltip updates
            this._tooltipUpdateInterval = setInterval(function () {
                $('.qtip .rel-time').each(function (i, el) {
                    el = $(el);
                    var ds = new Date().getTime() / 1000 - el.data('actiontime');
                    el.html(relativeTime(ds));
                });
                var d = new Date(), datetime = d.toTimeString().substr(0, 8);
                $('.realTimeMap_datetime').html(datetime);
            }, 1000);
        },

        /*
         * resizes the map to widget dimensions
         */
        resize: function () {
            var ratio, w, h, map = this.map;
            ratio = map.viewAB.width / map.viewAB.height;
            w = map.container.width();
            h = Math.min(w / ratio, $(window).height() - 30);

            var radScale = Math.pow((h * ratio * h) / 130000, 0.3);
            this.maxRad = 10 * radScale;
            this.minRad = 4 * radScale;

            map.container.height(h - 2);
            map.resize(w, h);
            if (map.symbolGroups && map.symbolGroups.length > 0) {
                map.symbolGroups[0].update();
            }

            if (w < 355) $('.tableIcon span').hide();
            else $('.tableIcon span').show();
        },

        _destroy: function () {
            UIControl.prototype._destroy.call(this);

            if (this._tooltipUpdateInterval) {
                clearInterval(this._tooltipUpdateInterval);
            }

            $(window).off('resize.' + this.uniqueId);

            this.map.clear();
            $(this.map.container).html('');
            delete this.map;
        }

    });

}());

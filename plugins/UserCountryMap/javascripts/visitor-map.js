/*!
 * Matomo - free/libre analytics platform
 *
 * Visitors Map with zoom in continents / countries. Cities + Region view.
 * Using Kartograph.js http://kartograph.org/
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {

    // create a global namespace for UserCountryMap plugin
    // this is used both by visitor map and realtime map
    window.UserCountryMap = window.UserCountryMap || {};

    // the main class for this widget, provides the interface for the template
    var VisitorMap = window.UserCountryMap.VisitorMap = function (config, theWidget) {
        this.config = config;
        this.theWidget = theWidget || false;
        this.run();
    };

    $.extend(VisitorMap.prototype, {

        /*
         * initializes the map after widget creation
         */
        run: function () {
            var self = this,
                config = self.config,
                colorManager = piwik.ColorManager,
                colorNames = ['no-data-color', 'one-country-color', 'color-range-start-choropleth',
                              'color-range-start-normal', 'color-range-end-choropleth', 'color-range-end-normal',
                              'country-highlight-color', 'unknown-region-fill-color', 'unknown-region-stroke-color',
                              'region-stroke-color', 'invisible-region-background', 'city-label-color',
                              'city-stroke-color', 'city-highlight-stroke-color', 'city-highlight-fill-color',
                              'city-highlight-label-color', 'city-label-fill-color', 'city-selected-color',
                              'city-selected-label-color', 'region-layer-stroke-color', 'country-selected-color',
                              'region-selected-color', 'region-highlight-color', 'inset-stroke-color'],
                colors = colorManager.getColors('visitor-map', colorNames),
                noDataColor = colors['no-data-color'],
                oneCountryColor = colors['one-country-color'],
                colorRangeStartChoropleth = colors['color-range-start-choropleth'],
                colorRangeStartNormal = colors['color-range-start-normal'],
                colorRangeEndChoropleth = colors['color-range-end-choropleth'],
                colorRangeEndNormal = colors['color-range-end-normal'],
                specialMetricsColorScale = colorManager.getColors(
                    'visitor-map',
                    ['special-metrics-color-scale-1', 'special-metrics-color-scale-2', 'special-metrics-color-scale-3',
                     'special-metrics-color-scale-4'],
                    true
                ),
                countryHighlightColor = colors['country-highlight-color'],
                countrySelectedColor = colors['country-selected-color'],
                unknownRegionFillColor = colors['unknown-region-fill-color'],
                unknownRegionStrokeColor = colors['unknown-region-stroke-color'],
                regionStrokeColor = colors['region-stroke-color'],
                regionSelectedColor = colors['region-selected-color'],
                regionHighlightColor = colors['region-highlight-color'],
                invisibleRegionBackgroundColor = colors['invisible-region-background'],
                cityLabelColor = colors['city-label-color'],
                cityLabelFillColor = colors['city-label-fill-color'],
                cityStrokeColor = colors['city-stroke-color'],
                cityHighlightStrokeColor = colors['city-highlight-stroke-color'],
                cityHighlightFillColor = colors['city-highlight-fill-color'],
                cityHighlightLabelColor = colors['city-highlight-label-color'],
                citySelectedColor = colors['city-selected-color'],
                citySelectedLabelColor = colors['city-selected-label-color'],
                regionLayerStrokeColor = colors['region-layer-stroke-color'],
                insetStrokeColor = colors['inset-stroke-color'],
                hasUserZoomed = false;

            /*
             * our own custom selector to only select stuff of this widget
             */
            function $$(selector) {
                return $(selector, self.theWidget ? self.theWidget.element : undefined);
            }

            var mapContainer = $$('.UserCountryMap_map').get(0),
                map = self.map = $K.map(mapContainer),
                main = $$('.UserCountryMap_container'),
                width = main.width(),
                _ = config._;

            config.noDataColor = noDataColor;
            self.widget = $$('.widgetUserCountryMapvisitorMap').parent();

            //window.__mapInstances = window.__mapInstances || [];
            //window.__mapInstances.push(map);

            function _reportParams(module, action, countryFilter) {
                var params = $.extend(config.reqParams, {
                    module: 'API',
                    method: 'API.getProcessedReport',
                    apiModule: module,
                    apiAction: action,
                    filter_limit: -1,
                    limit: -1,
                    format_metrics: 0,
                    showRawMetrics: 1
                });
                if (countryFilter) {
                    $.extend(params, {
                        filter_column: 'country',
                        filter_sort_column: 'nb_visits',
                        filter_pattern: countryFilter
                    });
                }
                return params;
            }

            /*
             * wrapper around jQuery.ajax, moves token_auth parameter
             * to POST data while keeping other parameters as GET
             */
            function ajax(params, dataType) {
                dataType = dataType || 'json';
                params = $.extend({}, params);
                var token_auth = '' + params.token_auth;
                delete params['token_auth'];
                return $.ajax({
                    url: 'index.php?' + $.param(params),
                    dataType: dataType,
                    data: { token_auth: token_auth, force_api_session: broadcast.isWidgetizeRequestWithoutSession() ? 0 : 1 },
                    type: 'POST'
                });
            }

            function minmax(values) {
                values = values.sort(function (a, b) { return Number(a) - Number(b); });
                return {
                    min: values[0],
                    max: values[values.length - 1],
                    median: values[Math.floor(values.length * 0.5)],
                    p33: values[Math.floor(values.length * 0.33)],
                    p66: values[Math.floor(values.length * 0.66)],
                    p90: values[Math.floor(values.length * 0.9)]
                };
            }

            function formatNumber(v, metric, first) {
                v = Number(v);

                if (v > 1000000) {
                    return (v / 1000000).toFixed(1) + 'm';
                }

                if (v > 1000) {
                    return (v / 1000).toFixed(1) + 'k';
                }

                if (!metric) {
                    return v;
                }

                if (metric == 'avg_time_on_site') {
                    v += first ? ' sec' : 's';
                } else if (metric == 'bounce_rate') {
                    v += '%';
                } else if (metric === 'nb_actions_per_visit') {
                    if (parseInt(v, 10) === v) {
                        return v;
                    }

                    return v.toFixed(1);
                }

                return v;
            }

            //
            // Since some metrics are transmitted in an non-numeric format like
            // "61.45%", we need to parse the numbers to make sure they can be
            // used for color scales etc. The parsed metrics will be stored as
            // METRIC_raw
            //
            function formatValueForTooltips(data, metric, id) {

                var val = data[metric] % 1 === 0 || Number(data[metric]) != data[metric] ? data[metric] : data[metric].toFixed(1);
                if (metric == 'bounce_rate') {
                    val = NumberFormatter.formatPercent(val);
                } else if (metric == 'avg_time_on_site') {
                    val = new Date(0, 0, 0, val / 3600, val % 3600 / 60, val % 60)
                        .toTimeString()
                        .replace(/.*(\d{2}:\d{2}:\d{2}).*/, "$1");
                } else {
                    val = NumberFormatter.formatNumber(val);
                }

                var v = _[metric].replace('%s', '<strong>' + val + '</strong>');

                if (val == 1 && metric == 'nb_visits') v = _.one_visit;

                if (metric.slice(0, 3) == 'nb_' && metric != 'nb_actions_per_visit') {
                    var total;
                    if (id.length == 3) total = UserCountryMap.countriesByIso[id][metric];
                    else if (id == 'world') total = self.config.visitsSummary[metric];
                    else {
                        total = 0;
                        $.each(UserCountryMap.countriesByIso, function (iso, country) {
                            if (UserCountryMap.ISO3toCONT[iso] == id) {
                                total += country[metric];
                            }
                        });
                    }
                    if (total) {
                        v += ' (' + formatPercentage(data[metric] / total) + ')';
                    }
                } else if (metric == 'avg_time_on_site') {
                    v += '<br/> (' + _.nb_visits.replace('%s', data.nb_visits) + ')';
                }
                return v;
            }

            function getColorScale(rows, metric, filter, choropleth) {

                var colscale;

                function addLegendItem(val, first) {
                    var d = $('<div>'), r = $('<div>'), l = $('<div>'),
                        metric = $$('.userCountryMapSelectMetrics').val(),
                        v = formatNumber(Math.round(val), metric, first);

                    d.css({ width: 17, height: 17, float: 'left', background: colscale(val) });
                    l.css({ 'margin-left': 20, 'line-height': '20px', 'text-align': 'right' }).html(v);
                    r.css({ clear: 'both', height: 19 });
                    r.append(d).append(l);
                    $('.UserCountryMap-legend .content').append(r);
                }

                var stats, values = [], id = self.lastSelected, c, showLegend;

                $.each(rows, function (i, r) {
                    if (!$.isFunction(filter) || filter(r)) {
                        var v = quantify(r, metric);
                        if (!isNaN(v)) values.push(v);
                    }
                });

                stats = minmax(values);
                showLegend = values.length > 0;

                if (stats.min == stats.max) {
                    colscale = function () { return chroma.hex(oneCountryColor); };
                    if (choropleth) {
                        $('.UserCountryMap-legend .content').html('').show();
                        if (showLegend) {
                            addLegendItem(stats.min, true);
                        }
                    }
                    return colscale;
                }

                colscale = chroma.scale()
                    .range([choropleth ? colorRangeStartChoropleth : colorRangeStartNormal,
                            choropleth ? colorRangeEndChoropleth : colorRangeEndNormal])
                    .domain(values, 4, 'c')
                    .mode('lch');

                if (metric == 'avg_time_on_site' || metric == 'nb_actions_per_visit' || metric == 'bounce_rate') {
                    if (id.length == 3) {
                        c = (stats.p90 - stats.min) / (stats.max - stats.min);
                        colscale = chroma.scale(specialMetricsColorScale, [0, c, c + 0.001, 1])
                            .domain(chroma.limits(rows, 'c', 5, 'curMetric', filter), 4, 'c')
                            .mode('hsl');
                    }
                }

                // a good place to update the legend, isn't it?
                if (choropleth && showLegend) {
                    $('.UserCountryMap-legend .content').html('').show();
                    var itemExists = {};
                    $.each(chroma.limits(values, 'k', 3), function (i, v) {
                        if (itemExists[v]) return;
                        addLegendItem(v, i === 0);
                        itemExists[v] = true;
                    });

                } else {
                    $('.UserCountryMap-legend .content').hide();
                }

                return colscale;
            }

            function formatPercentage(val) {
                if (val < 0.001) {
                    return '< ' + NumberFormatter.formatPercent(0.1);
                }
                return NumberFormatter.formatPercent(Math.round(1000 * val) / 10);
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
            // Save a reference to the function so it can be cleanly removed
            // as a listener later.
            self._onResizeLazy = onResizeLazy;

            function activateButton(btn) {
                $$('.UserCountryMap-view-mode-buttons a').removeClass('activeIcon');
                btn.addClass('activeIcon');
                $$('.UserCountryMap-activeItem').offset({ left: btn.offset().left });
            }

            function initUserInterface() {
                // react to changes of country select
                $$('.userCountryMapSelectCountry').off('change').change(function () {
                    hasUserZoomed = true;
                    updateState($$('.userCountryMapSelectCountry').val());
                });

                function zoomOut() {
                    hasUserZoomed = true;
                    var t = self.lastSelected,
                        tgt = 'world';  // zoom out to world per default..
                    if (t.length == 3 && UserCountryMap.ISO3toCONT[t] !== undefined) {
                        tgt = UserCountryMap.ISO3toCONT[t];  // ..but zoom to continent if we know it
                    }
                    updateState(tgt);
                }

                // enable zoom-out
                $$('.UserCountryMap-btn-zoom').off('click').click(zoomOut);
                $$('.UserCountryMap_map').off('click').click(zoomOut);

                // handle window resizes
                $(window).resize(onResizeLazy);

                // enable metric changes
                $$('.userCountryMapSelectMetrics').off('change').change(function () {
                    updateState(self.lastSelected);
                });

                // handle city button
                (function (btn) {
                    btn.off('click').click(function () {
                        if (self.lastSelected.length == 3) {
                            if (self.mode != "city") {
                                self.mode = "city";
                                hasUserZoomed = true;
                                updateState(self.lastSelected);
                            }
                        }
                    });
                })($$('.UserCountryMap-btn-city'));

                // handle region button
                (function (btn) {
                    btn.off('click').click(function () {
                        if (self.mode != "region") {
                            $$('.UserCountryMap-view-mode-buttons a').removeClass('activeIcon');
                            self.mode = "region";
                            hasUserZoomed = true;
                            updateState(self.lastSelected);
                        }
                    });
                })($$('.UserCountryMap-btn-region'));

                // add loading indicator overlay

                var bl = $('<div id="UserCountryMap-black"></div>');
                bl.hide();
                $$('.UserCountryMap_map').append(bl);

                var infobtn = $('.UserCountryMap-info-btn');
                infobtn.off('mouseenter').on('mouseenter',function (e) {
                    $(infobtn.data('tooltip-target')).show();
                }).off('mouseleave').on('mouseleave', function (e) {
                    $(infobtn.data('tooltip-target')).hide();
                });
                $('.UserCountryMap-tooltip').hide();
            }

            /*
             * updateState, called whenever the view changes
             */
            function updateState(id) {
                // double check view mode
                if (self.mode == "city" && id.length != 3) {
                    // city mode is reserved for country views
                    self.mode = "region";
                }

                var metric = $$('.userCountryMapSelectMetrics').val();
                // store current map state
                self.widget.dashboardWidget('setParameters', {
                    lastMap: id, viewMode: self.mode, lastMetric: metric
                });

                $('.UserCountryMap-info-btn').hide();

                try {
                    // check which map to render
                    if (id.length == 3) {
                        // country map
                        renderCountryMap(id, metric);
                    } else {
                        // world or continent map
                        renderWorldMap(id, metric);
                    }

                } catch (e) {
                    // console.error(e);
                    $('.UserCountryMap-info .content').html(e);
                    $('.UserCountryMap-info').show();
                }

                _updateUI(id, metric);

                self.lastSelected = id;
            }

            /*
             * update the widgets ui according to the currently selected view
             */
            function _updateUI(id, metric) {
                // update UI
                if (self.mode == "city") {
                    activateButton($$('.UserCountryMap-btn-city'));
                } else {
                    activateButton($$('.UserCountryMap-btn-region'));
                }
                var countrySelect = $$('.userCountryMapSelectCountry');
                countrySelect.val(id);

                var zoom = $$('.UserCountryMap-btn-zoom');
                if (id == 'world') zoom.addClass('inactiveIcon');
                else zoom.removeClass('inactiveIcon');

                // show flag icon in select box
                var flag = $$('.userCountryMapFlag'),
                    regionBtn = $$('.UserCountryMap-btn-region');
                if (id.length == 3) {
                    if (UserCountryMap.countriesByIso[id]) {  // we have visits in this country
                        flag.css({
                            'background-image': 'url(' + UserCountryMap.countriesByIso[id].flag + ')',
                            'background-repeat': 'no-repeat',
                            'background-position': '5px 5px'
                        });
                        $$('.UserCountryMap-btn-city').removeClass('inactiveIcon').show();
                        $('span', regionBtn).html(regionBtn.data('region'));
                    } else {
                        // not a single visit in this country
                        $$('.UserCountryMap-btn-city').addClass('inactiveIcon');
                        $('.map-stats').html(_.no_data);
                        $('.map-title').html('');
                        return;
                    }

                } else {
                    flag.css({
                        'background': 'none'
                    });
                    $$('.UserCountryMap-btn-city').addClass('inactiveIcon').hide();
                    $('span', regionBtn).html(regionBtn.data('country'));
                }

                var mapTitle = id.length == 3 ?
                        UserCountryMap.countriesByIso[id].name :
                        $$('.userCountryMapSelectCountry option[value=' + id + ']').html(),
                    totalVisits = 0,
                    totalMetricValue = 0;
                // update map title
                $('.map-title').html(mapTitle);
                $$('.widgetUserCountryMapvisitorMap .widgetName .map-title').html(' – ' + mapTitle);
                // update total visits for that region
                if (id.length == 3) {
                    totalVisits = UserCountryMap.countriesByIso[id]['nb_visits'];
                    totalMetricValue = UserCountryMap.countriesByIso[id][metric];
                } else if (id.length == 2) {
                    $.each(UserCountryMap.countriesByIso, function (iso, country) {
                        if (UserCountryMap.ISO3toCONT[iso] == id) {
                            totalVisits += country['nb_visits'];
                            totalMetricValue += country[metric];
                        }
                    });
                } else {
                    totalVisits = self.config.visitsSummary['nb_visits'];
                    totalMetricValue = self.config.visitsSummary[metric];
                }

                var data = {};
                data[metric] = totalMetricValue;
                $('.map-stats').html(
                    '<strong>' + formatValueForTooltips(data, metric, false) + '</strong>' +
                    (id != 'world' ? (' (' + formatPercentage(totalMetricValue / self.config.visitsSummary[metric]) + ')') : '')
                );
            }

            /*
             * called by updateState if either the world or a continent is selected
             */
            function renderWorldMap(target, metric) {

                /**
                 * update the colors of the countrys
                 */
                function updateColorsAndTooltips(metric) {

                    // Create a chroma ColorScale for the selected metric that regards only the
                    // countries that are visible in the map.
                    colscale = getColorScale(UserCountryMap.countryData, metric, function (r) {
                        if (target.length == 2) {
                            return UserCountryMap.ISO3toCONT[r.iso] == target;
                        } else {
                            return true;
                        }
                    }, true);

                    function countryFill(data) {
                        var d = UserCountryMap.countriesByIso[data.iso];
                        if (d === null) {
                            return self.config.noDataColor;
                        } else {
                            return colscale(d[metric]);
                        }
                    }

                    var countryLayer = map.getLayer('countries');
                    if(countryLayer) {
                        // Apply the color scale to the map.
                        countryLayer
                        .style('fill', countryFill)
                        .on('mouseenter', function (d, path, evt) {
                            if (evt.shiftKey) { // highlight on mouseover with shift pressed
                                path.attr('fill', countryHighlightColor);
                            }
                        })
                        .on('mouseleave', function (d, path, evt) {
                            if ($.inArray(UserCountryMap.countriesByIso[d.iso].name, _rowEvolution.labels) == -1) {
                                path.attr('fill', countryFill(d)); // reset color
                            }
                        });

                        // Update the map tooltips.
                        countryLayer.tooltips(function (data) {
                            var metric = $$('.userCountryMapSelectMetrics').val(),
                                country = UserCountryMap.countriesByIso[data.iso];
                            return '<h3>' + country.name + '</h3>' +
                                formatValueForTooltips(country, metric, target);
                        });
                    }

                }

                // if the view hasn't changed (but probably the selected metric),
                // all we need to do is to recolor the current map.
                if (target == self.lastSelected) {
                    updateColorsAndTooltips(metric);
                    return;
                }

                // otherwise we need to load another map svg
                _updateMap(target + '.svg', function () {

                    // add a layer for non-selectable countries = for which no data is
                    // defined in the current report
                    map.addLayer('countries', {
                        name: 'context',
                        filter: function (pd) {
                            return UserCountryMap.countriesByIso[pd.iso] === undefined;
                        },
                        tooltips: function (pd) {
                            var countryName = pd.name;
                            for (var iso in self.config.countryNames) {
                                if (UserCountryMap.ISO2toISO3[iso.toUpperCase()] == pd.iso) {
                                    countryName = self.config.countryNames[iso];
                                    break;
                                }
                            }
                            return '<h3>' + countryName + '</h3>' + _.no_visit;
                        }
                    });

                    // add a layer for selectable countries = for which we have data
                    // available in the current report
                    map.addLayer('countries', { name: 'countryBG', filter: function (pd) {
                        return UserCountryMap.countriesByIso[pd.iso] !== undefined;
                    }});

                    map.addLayer('countries', {
                        key: 'iso',
                        filter: function (pd) {
                            return UserCountryMap.countriesByIso[pd.iso] !== undefined;
                        },
                        click: function (data, path, evt) {
                            evt.stopPropagation();
                            if (evt.shiftKey || _rowEvolution.labels.length) {
                                if (evt.altKey) {
                                    path.attr('fill', countrySelectedColor);
                                    addMultipleRowEvolution('getCountry', UserCountryMap.countriesByIso[data.iso].name);
                                } else {
                                    showRowEvolution('getCountry', UserCountryMap.countriesByIso[data.iso].name);
                                    updateColorsAndTooltips(metric);
                                }
                                return;
                            }
                            var tgt;
                            if (self.lastSelected != 'world' || UserCountryMap.countriesByIso[data.iso] === undefined) {
                                tgt = data.iso;
                            } else {
                                tgt = UserCountryMap.ISO3toCONT[data.iso];
                            }
                            hasUserZoomed = true;
                            updateState(tgt);
                        }
                    });

                    updateColorsAndTooltips(metric);
                });
            }

            /*
             * updateMap is called by renderCountryMap() and renderWorldMap()
             */
            function _updateMap(svgUrl, callback) {
                map.loadMap(config.svgBasePath + svgUrl, function () {

                    map.clear();
                    UserCountryMap.routeInsetDots(map);
                    self.resize();
                    callback();

                    $('.ui-tooltip').remove(); // remove all existing tooltips

                }, { padding: -3});
            }

            function indicateLoading() {
                $$('.UserCountryMap-black').show();
                $$('.UserCountryMap-black').css('opacity', 0);
                $$('.UserCountryMap-black').animate({ opacity: 0.5 }, 400);
                $$('.UserCountryMap .loadingPiwik').show();
            }

            function loadingComplete() {
                $$('.UserCountryMap-black').hide();
                $$('.UserCountryMap .loadingPiwik').hide();
            }

            /*
             * returns a quantifiable value for a given metric
             */
            function quantify(d, metric) {
                if (!metric) metric = $$('.userCountryMapSelectMetrics').val();
                switch (metric) {
                    default:
                        return d[metric];
                }
            }

            /*
             * Aggregates a list of report rows by a given grouping function
             *
             * the groupBy function gets a row as argument add should return a
             * group-id or false, if the row should be ignored.
             *
             * all rows for which groupBy returns the same group-id are
             * aggregated according to the given metric.
             */
            function aggregate(rows, groupBy) {

                var groups = {};
                $.each(rows, function (i, row) {
                    var g_id = groupBy ? groupBy(row) : 'X';
                    g_id = g_id === true ? $.isNumeric(i) && i === Number(i) ? false : i : g_id;
                    if (g_id) {
                        if (!groups[g_id]) {
                            groups[g_id] = {
                                nb_visits: 0,
                                nb_actions: 0,
                                sum_visit_length: 0,
                                bounce_count: 0
                            };
                        }
                        $.each(groups[g_id], function (metric) {
                            groups[g_id][metric] += row[metric];
                        });
                    }
                });

                $.each(groups, function (g_id, group) {
                    var apv = group.nb_actions / group.nb_visits,
                        ats = group.sum_visit_length / group.nb_visits,
                        br = group.bounce_count / group.nb_visits;
                    group['nb_actions_per_visit'] = apv;
                    group['avg_time_on_site'] = new Date(0, 0, 0, ats / 3600, ats % 3600 / 60, ats % 60).toLocaleTimeString();
                    group['bounce_rate'] = (br % 1 !== 0 ? br.toFixed(1) : br) + "%";
                });

                return groupBy ? groups : groups.X;
            }

            function displayUnlocatableCount(unlocated, total, regionOrCity) {

                if (0 == unlocated) {
                    return;
                }

                $('.unlocated-stats').html(
                    _pk_translate('UserCountryMap_Unlocated', [
                        unlocated,
                        '(' + formatPercentage(unlocated / total) + ')',
                        UserCountryMap.countriesByIso[self.lastSelected].name
                    ])
                );
                $('.UserCountryMap-info-btn').show();

                var zoomTitle = '';
                if (regionOrCity == 'region') {
                    zoomTitle = ' ' + _pk_translate('UserCountryMap_WithUnknownRegion', [unlocated]);
                } else if (regionOrCity == 'city') {
                    zoomTitle = ' ' + _pk_translate('UserCountryMap_WithUnknownCity', [unlocated]);
                }

                if (unlocated && zoomTitle) {
                    if ($('.map-stats .unlocatableCount').length) {
                        $('.map-stats .unlocatableCount').html(zoomTitle);
                    } else {
                        $('.map-stats').append('<small class="unlocatableCount">' + zoomTitle + '</small>');
                    }
                }
            }

            /*
             * renders a country map (either region or city view)
             */
            function renderCountryMap(iso) {

                var countryMap = {
                    zoomed: false,
                    lastRequest: false,
                    lastResponse: false
                };

                /*
                 * updates the colors in the current region map
                 * this happens once a new country is loaded and
                 * whenever the metric changes
                 */
                function updateRegionColors() {
                    indicateLoading();
                    // load data from Piwik API
                    ajax(_reportParams('UserCountry', 'getRegion', UserCountryMap.countriesByIso[iso].iso2))
                        .done(function (data) {
                            convertBounceRatesToPercents(data);

                            loadingComplete();

                            var regionDict = {},
                                totalCountryVisits = UserCountryMap.countriesByIso[iso].nb_visits,
                                unlocated = totalCountryVisits;
                            // self.lastReportMetricStats = {};

                            // Region paths carry their ISO 3166-2 subdivision code
                            // in data-region, which is exactly the region code the
                            // API returns, so no translation is needed.
                            function regionCode(region) {
                                return region.region;
                            }

                            function regionExistsInMap(code) {
                                return map.getLayer('regions').getPaths({ region: code }).length > 0;
                            }

                            $.each(data.reportData, function (i, row) {

                                var region = data.reportMetadata[i].region;

                                if (!regionExistsInMap(region)) {
                                    // fall back to matching the postal code (data-p)
                                    var q = {
                                        'p': region
                                    };

                                    if (map.getLayer('regions').getPaths(q).length) {
                                        region = map.getLayer('regions').getPaths(q)[0].data.region;
                                    }
                                }

                                regionDict[region] = $.extend(row, data.reportMetadata[i], {
                                    curMetric: quantify(row, metric)
                                });
                            });

                            var metric = $$('.userCountryMapSelectMetrics').val();

                            // Whole-country fallback: countries without a regional
                            // breakdown are drawn as a single shape tagged "__ALL__".
                            // Colour it with the country total so region mode shows a
                            // number instead of a blank outline. label is left unset
                            // so it isn't treated as a clickable (region) row. The
                            // generator only emits __ALL__ for a country with no other
                            // region shapes, so a map never carries both __ALL__ and real
                            // regions; the anyRegionMatched check below is a defensive
                            // guard for that invariant -- were it ever violated, adding
                            // __ALL__ would subtract the country total twice in the
                            // unlocated count below.
                            //
                            // A map is __ALL__ when the geolocation providers (MaxMind /
                            // DB-IP) emit no region code for that country -- which is
                            // decided by what the providers actually return, not by
                            // whether ISO 3166-2 defines subdivisions. Some countries
                            // (e.g. Madagascar) have real ISO provinces but providers
                            // report an empty region for every visit, so they are drawn
                            // as __ALL__ on purpose; individual provinces would only ever
                            // render permanently grey. This is intentional, not a lost
                            // regional breakdown.
                            if (regionExistsInMap('__ALL__')) {
                                var anyRegionMatched = false;
                                $.each(regionDict, function (code) {
                                    if (code !== '__ALL__' && regionExistsInMap(code)) {
                                        anyRegionMatched = true;
                                        return false;
                                    }
                                });
                                if (!anyRegionMatched) {
                                    var whole = UserCountryMap.countriesByIso[iso];
                                    regionDict['__ALL__'] = $.extend({}, whole, {
                                        curMetric: quantify(whole, metric),
                                        label: null
                                    });
                                }
                            }

                            $.each(regionDict, function (key, region) {
                                if (regionExistsInMap(key)) unlocated -= region.nb_visits;
                            });
                            displayUnlocatableCount(unlocated, totalCountryVisits, 'region');

                            // create color scale
                            colscale = getColorScale(regionDict, 'curMetric', null, true);

                            function regionFill(data) {
                                var code = regionCode(data);
                                return regionDict[code] === undefined ? unknownRegionFillColor : colscale(regionDict[code].curMetric);
                            }

                            // apply colors to map
                            map.getLayer('regions')
                                .style('fill', regionFill)
                                .style('stroke',function (data) {
                                    return regionDict[regionCode(data)] === undefined ? unknownRegionStrokeColor : regionStrokeColor;
                                }).sort(function (data) {
                                    var code = regionCode(data);
                                    return regionDict[code] === undefined ? -1 : regionDict[code].curMetric;
                                }).tooltips(function (data) {
                                    var metric = $$('.userCountryMapSelectMetrics').val(),
                                        region = regionDict[regionCode(data)];
                                    if (region === undefined) {
                                        return '<h3>' + data.name + '</h3><p>' + _.nb_visits.replace('%s', '<strong>0</strong>') + '</p>';
                                    }
                                    return '<h3>' + data.name + '</h3>' +
                                        formatValueForTooltips(region, metric, iso);
                                }).on('click',function (d, path, evt) {
                                    var region = regionDict[regionCode(d)];
                                    if (region && region.label) {
                                        if (evt.shiftKey) {
                                            path.attr('fill', regionSelectedColor);
                                            addMultipleRowEvolution('getRegion', region.label);
                                        } else {
                                            map.getLayer('regions').style('fill', regionFill);
                                            showRowEvolution('getRegion', region.label);
                                        }
                                    }
                                }).on('mouseenter',function (d, path, evt) {
                                    var region = regionDict[regionCode(d)];
                                    if (region && region.label) {
                                        if (evt.shiftKey) {
                                            path.attr('fill', regionHighlightColor);
                                        }
                                    }
                                }).on('mouseleave',function (d, path, evt) {
                                    var region = regionDict[regionCode(d)];
                                    if (region && region.label) {
                                        if ($.inArray(region.label, _rowEvolution.labels) == -1) {
                                            // reset color
                                            path.attr('fill', regionFill(d));
                                        }
                                    }
                                }).style('cursor', function (d) {
                                    return regionDict[regionCode(d)] && regionDict[regionCode(d)].label ? 'pointer' : 'default';
                                });

                            // check for regions missing in the map, but skip
                            // whole-country __ALL__ maps where individual API region
                            // rows never have a matching shape by design
                            if (!regionExistsInMap('__ALL__')) {
                                $.each(regionDict, function (code, region) {
                                    if (!regionExistsInMap(code)) {
                                        console.warn('possible region mismatch!', code, region.nb_visits);
                                    }
                                });
                            }
                        });
                }

                /*
                 * updates the city symbols in the current map
                 * this happens once a new country is loaded and
                 * whenever the metric changes
                 */
                function updateCitySymbols() {
                    // color regions in white as background for symbols
                    var layerName = self.mode != "region" ? "regions2" : "regions";
                    if (map.getLayer(layerName)) map.getLayer(layerName).style('fill', invisibleRegionBackgroundColor);

                    indicateLoading();

                    // get visits per city from API
                    ajax(_reportParams('UserCountry', 'getCity', UserCountryMap.countriesByIso[iso].iso2))
                        .done(function (data) {
                            convertBounceRatesToPercents(data);

                            loadingComplete();

                            var metric = $$('.userCountryMapSelectMetrics').val(),
                                colscale,
                                totalCountryVisits = UserCountryMap.countriesByIso[iso].nb_visits,
                                unlocated = totalCountryVisits,
                                cities = [];

                            // merge reportData and reportMetadata to cities array
                            $.each(data.reportData, function (i, row) {
                                unlocated -= row.nb_visits;
                                cities.push($.extend(row, data.reportMetadata[i], {
                                    curMetric: quantify(row, metric)
                                }));
                            });

                            displayUnlocatableCount(unlocated, totalCountryVisits, 'city');

                            // sort by current metric
                            cities.sort(function (a, b) { return b.curMetric - a.curMetric; });

                            colscale = getColorScale(cities, metric);

                            // construct scale
                            var radscale = $K.scale.linear(cities.concat({ curMetric: 0 }), 'curMetric');

                            var area = map.container.width() * map.container.height(),
                                sumArea = 0,
                                f = {
                                    nb_visits: 0.002,
                                    nb_uniq_visitors: 0.002,
                                    nb_actions: 0.002,
                                    avg_time_on_site: 0.02,
                                    nb_actions_per_visit: 0.02,
                                    bounce_rate: 0.02
                                },
                                maxRad;

                            $.each(cities, function (i, city) {
                                sumArea += isNaN(city.curMetric) ? 0 : Math.pow(radscale(city.curMetric), 2);
                            });

                            maxRad = Math.sqrt(area * f[metric] / sumArea);

                            radscale = $K.scale.sqrt(cities.concat({ curMetric: 0 }), 'curMetric').range([2, maxRad + 2]);

                            var citySymbols = map.addSymbols({
                                type: $K.LabeledBubble,
                                data: cities,
                                clustering: 'noverlap',
                                clusteringOpts: {
                                    size: 128,
                                    tolerance: 0
                                },
                                title: function (d) {
                                    var v = d.curMetric;
                                    if (isNaN(v)) {
                                        return '';
                                    }

                                    if (metric === 'bounce_rate') {
                                        v = Number((''+ v).replace('%', ''));
                                    } else if (metric === 'avg_time_on_site') {
                                        v = Number(v);
                                    }

                                    if (isNaN(v)) {
                                        return '';
                                    }

                                    if (radscale(v) > 10) {
                                        return formatNumber(d.curMetric, metric);
                                    }

                                    return '';
                                },
                                labelattrs: {
                                    fill: cityLabelColor,
                                    'font-size': 11,
                                    stroke: false,
                                    cursor: 'pointer'
                                },
                                filter: function (d) {
                                    if (isNaN(d.lat) || isNaN(d.long)) return false;
                                    return !!d.curMetric && d.curMetric !== '0';
                                },
                                aggregate: function (rows) {
                                    var row = aggregate(rows);
                                    row.city_names = [];
                                    row.label = rows[0].label; // keep label of biggest city for row evolution
                                    $.each(rows, function (i, r) {
                                        row.city_names = row.city_names.concat(r.city_names ? r.city_names : [r.city_name]);
                                    });
                                    row.city_name = row.city_names[0] + (row.city_names.length > 1 ? ' ' + _.and_n_others.replace('%s', (row.city_names.length - 1)) : '');
                                    row.curMetric = quantify(row, metric);
                                    return row;
                                },
                                sortBy: 'radius desc',
                                location: function (city) { return [city.long, city.lat]; },
                                radius: function (city) {
                                    var scale = radscale(city.curMetric);
                                    if (isNaN(scale)) {
                                        return 0.01;
                                    }
                                    return scale;
                                },
                                tooltip: function (city) {
                                    return '<h3>' + piwikHelper.htmlEntities(city.city_name) + '</h3>' +
                                        formatValueForTooltips(city, metric, iso);
                                },
                                attrs: function (city) {
                                    var color = colscale(city.curMetric);
                                    if (color && color.hex) {
                                        color = color.hex();
                                    }
                                    return {
                                        fill: color,
                                        'fill-opacity': 0.7,
                                        stroke: cityStrokeColor,
                                        cursor: 'pointer'
                                    };
                                },
                                mouseenter: function (city, symbol, evt) {
                                    symbol.path.attr({
                                        'fill-opacity': 1,
                                        'stroke': cityHighlightStrokeColor,
                                        'stroke-opacity': 1,
                                        'stroke-width': 2
                                    });
                                    if (evt.shiftKey) {
                                        symbol.path.attr({ fill: cityHighlightFillColor });
                                        if (symbol.label) symbol.label.attr({ fill: cityHighlightLabelColor });
                                    }
                                },
                                mouseleave: function (city, symbol) {
                                    symbol.path.attr({
                                        'fill-opacity': 0.7,
                                        'stroke-opacity': 1,
                                        'stroke-width': 1,
                                        'stroke': cityLabelColor
                                    });
                                    if ($.inArray(city.label, _rowEvolution.labels) == -1) {
                                        symbol.path.attr({ fill: colscale(city.curMetric) });
                                        if (symbol.label) symbol.label.attr({ fill: cityLabelFillColor });
                                    }
                                },
                                click: function (city, symbol, evt) {
                                    if (evt.shiftKey) {
                                        addMultipleRowEvolution('getCity', city.label);
                                        symbol.path.attr('fill', citySelectedColor);
                                        if (symbol.label) symbol.label.attr('fill', citySelectedLabelColor);
                                    } else {
                                        showRowEvolution('getCity', city.label);
                                        citySymbols.update({
                                            attrs: function (city) {
                                                return { fill: colscale(city.curMetric) };
                                            }
                                        });
                                    }
                                }
                            });
                        });
                }

                _updateMap(iso + '.svg', function () {

                    // add background
                    map.addLayer('context', {
                        key: 'iso',
                        filter: function (pd) {
                            return UserCountryMap.countriesByIso[pd.iso] === undefined;
                        }
                    });
                    map.addLayer('context', {
                        key: 'iso',
                        name: 'context-clickable',
                        filter: function (pd) {
                            return UserCountryMap.countriesByIso[pd.iso] !== undefined;
                        },
                        click: function (path, p, evt) {   // add click events for surrounding countries
                            evt.stopPropagation();
                            hasUserZoomed = true;
                            updateState(path.iso);
                        },
                        tooltips: function (data) {
                            if (UserCountryMap.countriesByIso[data.iso] === undefined) {
                                return 'no data';
                            }
                            var metric = $$('.userCountryMapSelectMetrics').val(),
                                country = UserCountryMap.countriesByIso[data.iso];
                            return '<h3>' + country.name + '</h3>' +
                                formatValueForTooltips(country, metric, 'world');
                        }
                    });
                    function isThisCountry(d) { return d.iso == iso;}

                    map.addLayer("context", {
                        name: "regionBG",
                        filter: isThisCountry
                    });
                    map.addLayer("context", {
                        name: "regionBG-fill",
                        filter: isThisCountry
                    });
                    map.addLayer('regions', {
                        key: 'region',
                        name: self.mode != "region" ? "regions2" : "regions",
                        styles: {
                            stroke: regionLayerStrokeColor
                        },
                        click: function (d, p, evt) {
                            evt.stopPropagation();
                        }
                    });
                    UserCountryMap.addInsetsLayer(map, insetStrokeColor);
                    var clickable = map.getLayer('context-clickable');

                    function filtCountryLabels(data) {
                        return data.iso != iso &&
                            clickable &&
                            clickable.getPath(data.iso) &&
                            Math.abs(clickable.getPath(data.iso).path.area()) > UserCountryMap.countryLabelMinArea;
                    }

                    function customLabelPos(data) {
                        var CLP = UserCountryMap.customLabelPositions;
                        return CLP[iso] && CLP[iso][data.iso];
                    }

                    // returns either the reference to the country polygon or a custom label
                    // position if defined in UserCountryMap.customLabelPositions
                    function countryLabelPos(data) {
                        return customLabelPos(data) || 'context-clickable.' + data.iso;
                    }

                    function anchorCountryLabels() {
                        var thisCountry = map.getLayerPath('regionBG', { iso: iso }),
                            canvas = map.viewAB.asBBox(),
                            bounds = [canvas.xmin, canvas.ymin, canvas.xmax, canvas.ymax];
                        $.each(clickable.getPathsData(), function (i, data) {
                            if (!filtCountryLabels(data) || customLabelPos(data)) {
                                return;
                            }
                            var neighbour = clickable.getPath(data.iso),
                                anchor = UserCountryMap.countryLabelPosition(
                                    neighbour.path,
                                    thisCountry ? thisCountry.path : null,
                                    bounds
                                );
                            if (anchor) {
                                neighbour.path.centroid = function () { return anchor; };
                            }
                        });
                    }

                    if (clickable) {
                        anchorCountryLabels();

                        map.addSymbols({
                            data: clickable.getPathsData(),
                            type: $K.Label,
                            filter: filtCountryLabels,
                            location: countryLabelPos,
                            text: function (data) { return UserCountryMap.countriesByIso[data.iso].iso2; },
                            'class': 'countryLabelBg'
                        });
                        map.addSymbols({
                            data: clickable.getPathsData(),
                            type: $K.Label,
                            filter: filtCountryLabels,
                            location: countryLabelPos,
                            text: function (data) { return UserCountryMap.countriesByIso[data.iso].iso2; },
                            'class': 'countryLabel'
                        });
                    }

                    if (!UserCountryMap.countriesByIso[iso]) return;

                    if (self.mode == "region") {
                        updateRegionColors();
                    } else {
                        updateCitySymbols();
                    }

                });
            }

            var _rowEvolution = { labels: [], method: false };

            function addMultipleRowEvolution(method, label) {
                if (method != _rowEvolution.method) {
                    _rowEvolution = { method: method, labels: [] };
                }
                _rowEvolution.labels.push(label);
            }

            /*
             * opens row evolution popover
             */
            function showRowEvolution(method, label, column) {
                var box = Piwik_Popover.showLoading('Row Evolution'),
                    multiple, oldLabels = _rowEvolution.labels.slice();

                multiple = method == _rowEvolution.method && _rowEvolution.labels.length > 0;

                if (multiple) {
                    _rowEvolution.labels.push(label);
                    $.each(_rowEvolution.labels, function (i, l) {
                        _rowEvolution.labels[i] = l.replace(/, /g, '%2C%20');
                    });
                }

                var requestParams = $.extend({}, {
                    apiMethod: 'UserCountry.' + method,
                    label: multiple ? _rowEvolution.labels.join(',') : label.replace(/, /g, '%2C%20'),
                    disableLink: 1,
                    module: 'CoreHome',
                    idSite: config.reqParams.idSite,
                    period: config.reqParams.period,
                    date: config.reqParams.date,
                    action: multiple ? 'getMultiRowEvolutionPopover' : 'getRowEvolutionPopover',
                    token_auth: config.reqParams.token_auth
                });

                if (column) { requestParams.column = column; }

                ajax(requestParams, 'html')
                    .done(function (html) {
                        Piwik_Popover.setContent(html);

                        // use the popover title returned from the server
                        var title = box.find('div.popover-title');
                        if (title.length) {
                            Piwik_Popover.setTitle(title.html());
                            title.remove();
                        }

                        box.find('.compare-container').hide();
                        box.find('.rowevolution-startmulti').hide();
                        box.find('.multirowevoltion-metric').off('change').change(function (e) {
                            _rowEvolution.labels = oldLabels;
                            showRowEvolution(method, label, box.find('.multirowevoltion-metric').val());
                        });
                    });

                _rowEvolution.labels = [];
            }

            // now load the metrics for all countries
            ajax(_reportParams('UserCountry', 'getCountry'))
                .done(function (report) {
                    convertBounceRatesToPercents(report);

                    var metrics = $$('.userCountryMapSelectMetrics option');
                    var countryData = [], countrySelect = $$('.userCountryMapSelectCountry'),
                        countriesByIso = {};
                    UserCountryMap.lastReportMetricStats = {};
                    // read api result to countryData and countriesByIso
                    $.each(report.reportData, function (i, data) {
                        var meta = report.reportMetadata[i],
                            country = {
                                name: data.label,
                                iso2: meta.code.toUpperCase(),
                                iso: UserCountryMap.ISO2toISO3[meta.code.toUpperCase()],
                                flag: meta.logo
                            };
                        $.each(metrics, function (i, metric) {
                            metric = $(metric).val();
                            country[metric] = data[metric];
                        });
                        countryData.push(country);
                        countriesByIso[country.iso] = country;
                    });
                    // sort countries by name
                    countryData.sort(function (a, b) { return a.name > b.name ? 1 : -1; });

                    // store country data globally
                    UserCountryMap.countryData = countryData;
                    UserCountryMap.countriesByIso = countriesByIso;

                    function postCSSLoad() {
                        // map stylesheets are loaded

                        // hide loading indicator
                        $$('.UserCountryMap .loadingPiwik').hide();

                        // start with default view (or saved state??)
                        var params = self.widget.dashboardWidget('getWidgetObject').parameters;
                        self.mode = params && params.viewMode ? params.viewMode : 'region';
                        if (params && params.lastMetric) $$('.userCountryMapSelectMetrics').val(params.lastMetric);
                        // alert('updateState: '+params && params.lastMap ? params.lastMap : 'world');

                        // populate country select
                        var isoCodes = [];
                        $.each(countryData, function (i, country) {
                            if (!!country.iso) {
                                isoCodes.push(country.iso);
                                countrySelect.append('<option value="' + country.iso + '">' + country.name + '</option>');
                            }
                        });

                        if (!hasUserZoomed && isoCodes.length === 1 && isoCodes[0] && isoCodes[0] !== 'UNK') {
                            updateState(isoCodes[0]);
                        } else {
                            updateState(params && params.lastMap ? params.lastMap : 'world');
                        }

                        initUserInterface();

                    }
                    // check if CSS is already loaded
                    if (!$("link[href='" + config.mapCssPath + "']").length) {
                        // not loaded
                        map.loadCSS(config.mapCssPath, postCSSLoad);
                    } else {
                        // already loaded
                        postCSSLoad();
                    }
                });

            function hideOverlay(e) {
                var overlay = $('.content', $(e.target).parents('.UserCountryMap-overlay'));
                if (overlay.data('locked')) return;
                overlay.data('locked', true);
                overlay.fadeOut(200);

                $$('.UserCountryMap').mouseleave(function () {
                    overlay.fadeIn(200);
                    $$('.UserCountryMap').parent().off('mouseleave');
                    setTimeout(function () {
                        overlay.data('locked', false);
                    }, 1000);
                });
                var offset = $$('.UserCountryMap').offset(),
                    dim = {
                        x: overlay.offset().left - offset.left,
                        y: overlay.offset().top - offset.top,
                        w: overlay.width(),
                        h: overlay.height()
                    };
                $$('.UserCountryMap').mousemove(function (e) {
                    var mx = e.pageX - offset.left, my = e.pageY - offset.top, pad = 20,
                        outside = mx < dim.x - pad || mx > dim.x + dim.w + pad || my < dim.y - pad || my > dim.y + dim.h + pad;
                    if (outside) {
                        $$('.UserCountryMap').parent().off('mouseleave');
                        setTimeout(function () {
                            overlay.fadeIn(200);
                            setTimeout(function () {
                                overlay.data('locked', false);
                            }, 1000);
                        }, 100);
                    }
                });
                /*setTimeout(function() {
                 overlay.fadeIn(1000);
                 }, 3000);*/
            }

            $('.UserCountryMap-overlay').off('mouseenter').on('mouseenter', hideOverlay);
            $$('.widgetUserCountryMapvisitorMap .widgetName span').remove();
            $$('.widgetUserCountryMapvisitorMap .widgetName').append('<span class="map-title"></span>');

            // converts bounce rate quotients to numeric percents, eg, .12 => 12
            function convertBounceRatesToPercents(report) {
                $.each(report.reportData, function (i, row) {
                    if (row['bounce_rate']) {
                        row['bounce_rate'] = parseFloat(row['bounce_rate']) * 100;
                    }
                });
            }
        },

        /*
         * resizes the map
         */
        resize: function () {
            var ratio, w, h,
                map = this.map;

            // Adaptive: the widget takes each loaded map's own aspect ratio, so
            // every map fills the box with no letterboxing/white space (the widget
            // height changes per map).
            ratio = map.viewAB.width / map.viewAB.height;
            w = map.container.width();
            h = w / ratio;

            // Fit the map height to the viewport. These two clamps are mutually
            // exclusive on purpose:
            //  - Widgetize iframe mode already accounts for the real chrome (window
            //    height minus the non-map page height), so use that alone. Applying
            //    the 0.85 clamp on top of it would reserve chrome space twice and
            //    leave ~15% of the frame permanently blank. The `.widget` ancestor
            //    gate is avoided because it also matched dashboard widgets and the
            //    AddWidget preview, where $('html').height() far exceeds the viewport
            //    and made maxHeight go negative.
            //  - Everywhere else (dashboard widget, standalone, AddWidget preview)
            //    nothing subtracts chrome, so cap a tall/portrait map (Liechtenstein,
            //    Chile, ...) at 85% of the viewport to keep it from overflowing and to
            //    leave room for widget chrome (title bar, controls).
            var maxViewportHeightRatio = 0.85;
            var winHeight = $(window).height();
            if (!this.theWidget && $('body').hasClass('widgetized')) {
                var maxHeight = winHeight - ($('html').height() - map.container.height());
                h = Math.min(maxHeight, h);
            } else if (winHeight > 0) {
                h = Math.min(h, winHeight * maxViewportHeightRatio);
            }

            map.container.height(h);
            map.resize(w, h);

            if (w < 355) $('.UserCountryMap .tableIcon span').hide();
            else $('.UserCountryMap .tableIcon span').show();
        },

        /*
         * removes the map
         */
        destroy: function () {
            this.map.clear();
            $(this.map.container).html('');
            $(window).off('resize', this._onResizeLazy)
        }

    });

}());

/*
 * Some static data used both by VisitorMap and RealtimeMap
 */
$.extend(UserCountryMap, {

    // Far-away regions (e.g. Canary Is., Svalbard, Alaska) are drawn in inset
    // boxes. When the loaded SVG defines an #insets group, add a non-interactive
    // layer that frames each box using the given (theme-aware) stroke colour.
    addInsetsLayer: function (map, strokeColor) {
        if (map.svgSrc && map.svgSrc.find('#insets').length) {
            map.addLayer('insets', {
                styles: { stroke: strokeColor, 'stroke-width': 0.7, fill: 'none' }
            });
        }
    },

    // kartograph places every visitor dot with the single mainland projection
    // (map.lonlat2xy). On maps with corner insets (e.g. USA's Alaska/Hawaii boxes) a
    // dot whose lon/lat lies inside an inset territory would be projected to its true
    // mainland position and fall off-frame, so route it into the matching inset box
    // instead. kartograph reads only the first <view> (the mainland projection);
    // each inset adds its own <view class="inset"> element (ignored by kartograph)
    // holding a <proj>, the inset's projected bounds x0/y0/x1/y1, and a scale/ox/oy
    // box transform. A point inside [x0,x1] x [y0,y1] maps to the box as
    // ((x - x0) * scale + ox, (y - y0) * scale + oy), after which viewBC scales svg
    // coords to the viewport like any symbol. Shared by visitor-map and realtime-map.
    routeInsetDots: function (map) {
        // always restore the stock projection first: a previously loaded map may
        // have installed inset routing that must not leak here.
        if (map._insetBase) { map.lonlat2xy = map._insetBase; }
        else { map._insetBase = map.lonlat2xy; }
        var stock = map._insetBase, insets = [];
        try {
            // map.svgSrc is kartograph's already-parsed SVG selection
            var views = map.svgSrc.find('view');
            for (var i = 0; i < views.length; i++) {
                var v = views[i];
                if (v.getAttribute('class') !== 'inset') continue;
                insets.push({
                    proj: $K.Proj.fromXML(v.getElementsByTagName('proj')[0]),
                    x0: +v.getAttribute('x0'), y0: +v.getAttribute('y0'),
                    x1: +v.getAttribute('x1'), y1: +v.getAttribute('y1'),
                    scale: +v.getAttribute('scale'),
                    ox: +v.getAttribute('ox'), oy: +v.getAttribute('oy')
                });
            }
        } catch (e) { return; } // malformed metadata -> keep stock projection
        if (!insets.length) return;
        map.lonlat2xy = function (loc) {
            var lon = (loc && loc.length >= 2) ? loc[0] : loc.lon;
            var lat = (loc && loc.length >= 2) ? loc[1] : loc.lat;
            for (var i = 0; i < insets.length; i++) {
                var iv = insets[i], pt = iv.proj.project(lon, lat);
                if (pt[0] >= iv.x0 && pt[0] <= iv.x1 && pt[1] >= iv.y0 && pt[1] <= iv.y1) {
                    // map the inset-projected point into the box, then let viewBC
                    // scale svg coords -> viewport like any other symbol.
                    return map.viewBC.project([
                        (pt[0] - iv.x0) * iv.scale + iv.ox,
                        (pt[1] - iv.y0) * iv.scale + iv.oy
                    ]);
                }
            }
            return stock.call(map, loc);
        };
    },

    countryLabelMinArea: 700,

    // half a two-letter label plus its halo, in svg units; wider than tall, like the glyphs
    countryLabelMargin: { x: 10, y: 6 },

    countryLabelPosition: function (path, avoid, bounds) {
        function ringArea(ring) {
            var a = 0, n = ring.length, i, p, q;
            for (i = 0; i < n; i++) {
                p = ring[i];
                q = ring[(i + 1) % n];
                a += p[0] * q[1] - q[0] * p[1];
            }
            return a / 2;
        }

        function ringBox(ring) {
            var box = [ring[0][0], ring[0][1], ring[0][0], ring[0][1]], i;
            for (i = 1; i < ring.length; i++) {
                box[0] = Math.min(box[0], ring[i][0]);
                box[1] = Math.min(box[1], ring[i][1]);
                box[2] = Math.max(box[2], ring[i][0]);
                box[3] = Math.max(box[3], ring[i][1]);
            }
            return box;
        }

        function covers(rings, boxes, x, y) {
            var crossings = 0, r, n, i, p, q, box;
            for (r = 0; r < rings.length; r++) {
                box = boxes[r];
                if (y < box[1] || y > box[3] || x > box[2]) {
                    continue;
                }
                n = rings[r].length;
                for (i = 0; i < n; i++) {
                    p = rings[r][i];
                    q = rings[r][(i + 1) % n];
                    if ((p[1] > y) !== (q[1] > y)
                        && x < p[0] + (y - p[1]) / (q[1] - p[1]) * (q[0] - p[0])
                    ) {
                        crossings++;
                    }
                }
            }
            return crossings % 2 === 1;
        }

        function boxDistance(box, x, y) {
            var dx = Math.max(box[0] - x, 0, x - box[2]),
                dy = Math.max(box[1] - y, 0, y - box[3]);
            return Math.sqrt(dx * dx + dy * dy);
        }

        function ringClearance(ring, x, y, min) {
            var n = ring.length, i, p, q, dx, dy, len, t, ex, ey, d;
            for (i = 0; i < n; i++) {
                p = ring[i];
                q = ring[(i + 1) % n];
                dx = q[0] - p[0];
                dy = q[1] - p[1];
                len = dx * dx + dy * dy;
                t = len ? Math.max(0, Math.min(1, ((x - p[0]) * dx + (y - p[1]) * dy) / len)) : 0;
                ex = x - (p[0] + t * dx);
                ey = y - (p[1] + t * dy);
                d = Math.sqrt(ex * ex + ey * ey);
                if (d < min) {
                    min = d;
                }
            }
            return min;
        }

        function clearance(measured, x, y, floor) {
            var min = Infinity, i;
            for (i = 0; i < measured.length; i++) {
                if (boxDistance(measured[i][1], x, y) >= min) {
                    continue;
                }
                min = ringClearance(measured[i][0], x, y, min);
                if (min <= floor) {
                    return min;
                }
            }
            return min;
        }

        var rings = (path && path.contours) || [],
            blocked = (avoid && avoid.contours) || [],
            boxes = [], blockedBoxes = [], measured = [], largest = 0, i, box,
            minX = 0, minY = 0, maxX = 0, maxY = 0;

        for (i = 0; i < rings.length; i++) {
            largest = Math.max(largest, Math.abs(ringArea(rings[i])));
            box = ringBox(rings[i]);
            boxes.push(box);
            measured.push([rings[i], box]);
            if (i === 0) {
                minX = box[0];
                minY = box[1];
                maxX = box[2];
                maxY = box[3];
            } else {
                minX = Math.min(minX, box[0]);
                minY = Math.min(minY, box[1]);
                maxX = Math.max(maxX, box[2]);
                maxY = Math.max(maxY, box[3]);
            }
        }
        if (!largest) {
            return null;
        }
        for (i = 0; i < blocked.length; i++) {
            box = ringBox(blocked[i]);
            blockedBoxes.push(box);
            measured.push([blocked[i], box]);
        }

        // The grid is aligned to the canvas, not to frame, so narrowing the frame only rejects
        // candidates instead of shifting the lattice and moving anchors it does not constrain.
        function search(frame) {
            var x0 = Math.max(minX, bounds[0]),
                y0 = Math.max(minY, bounds[1]),
                x1 = Math.min(maxX, bounds[2]),
                y1 = Math.min(maxY, bounds[3]),
                best = null, bestClearance = 0,
                gridSteps = 16, refinePasses = 4, factor = 3, step, pass;

            if (x1 < x0 || y1 < y0) {
                return null;
            }

            function scan(sx0, sx1, sy0, sy1, s) {
                var x, y, d;
                for (y = sy0; y <= sy1; y += s) {
                    for (x = sx0; x <= sx1; x += s) {
                        if (x < frame[0] || x > frame[2] || y < frame[1] || y > frame[3]
                            || !covers(rings, boxes, x, y) || covers(blocked, blockedBoxes, x, y)
                        ) {
                            continue;
                        }
                        d = clearance(measured, x, y, bestClearance);
                        if (d > bestClearance) {
                            bestClearance = d;
                            best = [x, y];
                        }
                    }
                }
            }

            step = Math.max(x1 - x0, y1 - y0) / gridSteps;
            if (!step) {
                return null;
            }

            scan(x0, x1, y0, y1, step);
            if (!best) {
                step /= factor;
                scan(x0, x1, y0, y1, step);
            }
            for (pass = 0; pass < refinePasses && best; pass++) {
                step /= factor;
                scan(best[0] - step * factor, best[0] + step * factor,
                     best[1] - step * factor, best[1] + step * factor, step);
            }

            return best;
        }

        // The label is drawn centred on the anchor and is wider than it, so keep the glyphs
        // off the canvas frame where the country leaves room for it.
        var margin = UserCountryMap.countryLabelMargin;

        return search([bounds[0] + margin.x, bounds[1] + margin.y,
                       bounds[2] - margin.x, bounds[3] - margin.y])
            || search(bounds);
    },

    // iso alpha-2 --> iso alpha-3
    ISO2toISO3: {"BD": "BGD", "BE": "BEL", "BF": "BFA", "BG": "BGR", "BA": "BIH", "BB": "BRB", "WF": "WLF", "BL": "BLM", "BM": "BMU", "BN": "BRN", "BO": "BOL", "BH": "BHR", "BI": "BDI", "BJ": "BEN", "BT": "BTN", "JM": "JAM", "BV": "BVT", "BW": "BWA", "WS": "WSM", "BQ": "BES", "BR": "BRA", "BS": "BHS", "JE": "JEY", "BY": "BLR", "BZ": "BLZ", "RU": "RUS", "RW": "RWA", "RS": "SRB", "TL": "TLS", "RE": "REU", "TM": "TKM", "TJ": "TJK", "RO": "ROU", "TK": "TKL", "GW": "GNB", "GU": "GUM", "GT": "GTM", "GS": "SGS", "GR": "GRC", "GQ": "GNQ", "GP": "GLP", "JP": "JPN", "GY": "GUY", "GG": "GGY", "GF": "GUF", "GE": "GEO", "GD": "GRD", "GB": "GBR", "GA": "GAB", "SV": "SLV", "GN": "GIN", "GM": "GMB", "GL": "GRL", "GI": "GIB", "GH": "GHA", "OM": "OMN", "TN": "TUN", "JO": "JOR", "HR": "HRV", "HT": "HTI", "HU": "HUN", "HK": "HKG", "HN": "HND", "HM": "HMD", "VE": "VEN", "PR": "PRI", "PS": "PSE", "PW": "PLW", "PT": "PRT", "SJ": "SJM", "PY": "PRY", "IQ": "IRQ", "PA": "PAN", "PF": "PYF", "PG": "PNG", "PE": "PER", "PK": "PAK", "PH": "PHL", "PN": "PCN", "PL": "POL", "PM": "SPM", "ZM": "ZMB", "EH": "ESH", "EE": "EST", "EG": "EGY", "ZA": "ZAF", "EC": "ECU", "IT": "ITA", "VN": "VNM", "SB": "SLB", "ET": "ETH", "SO": "SOM", "ZW": "ZWE", "SA": "SAU", "ES": "ESP", "ER": "ERI", "ME": "MNE", "MD": "MDA", "MG": "MDG", "MF": "MAF", "MA": "MAR", "MC": "MCO", "UZ": "UZB", "MM": "MMR", "ML": "MLI", "MO": "MAC", "MN": "MNG", "MH": "MHL", "MK": "MKD", "MU": "MUS", "MT": "MLT", "MW": "MWI", "MV": "MDV", "MQ": "MTQ", "MP": "MNP", "MS": "MSR", "MR": "MRT", "IM": "IMN", "UG": "UGA", "TZ": "TZA", "MY": "MYS", "MX": "MEX", "IL": "ISR", "FR": "FRA", "IO": "IOT", "SH": "SHN", "FI": "FIN", "FJ": "FJI", "FK": "FLK", "FM": "FSM", "FO": "FRO", "NI": "NIC", "NL": "NLD", "NO": "NOR", "NA": "NAM", "VU": "VUT", "NC": "NCL", "NE": "NER", "NF": "NFK", "NG": "NGA", "NZ": "NZL", "NP": "NPL", "NR": "NRU", "NU": "NIU", "CK": "COK", "XK": "XKX", "CI": "CIV", "CH": "CHE", "CO": "COL", "CN": "CHN", "CM": "CMR", "CL": "CHL", "CC": "CCK", "CA": "CAN", "CG": "COG", "CF": "CAF", "CD": "COD", "CZ": "CZE", "CY": "CYP", "CX": "CXR", "CS": "SCG", "CR": "CRI", "CW": "CUW", "CV": "CPV", "CU": "CUB", "SZ": "SWZ", "SY": "SYR", "SX": "SXM", "KG": "KGZ", "KE": "KEN", "SS": "SSD", "SR": "SUR", "KI": "KIR", "KH": "KHM", "KN": "KNA", "KM": "COM", "ST": "STP", "SK": "SVK", "KR": "KOR", "SI": "SVN", "KP": "PRK", "KW": "KWT", "SN": "SEN", "SM": "SMR", "SL": "SLE", "SC": "SYC", "KZ": "KAZ", "KY": "CYM", "SG": "SGP", "SE": "SWE", "SD": "SDN", "DO": "DOM", "DM": "DMA", "DJ": "DJI", "DK": "DNK", "VG": "VGB", "DE": "DEU", "YE": "YEM", "DZ": "DZA", "US": "USA", "UY": "URY", "YT": "MYT", "UM": "UMI", "LB": "LBN", "LC": "LCA", "LA": "LAO", "TV": "TUV", "TW": "TWN", "TT": "TTO", "TR": "TUR", "LK": "LKA", "LI": "LIE", "LV": "LVA", "TO": "TON", "LT": "LTU", "LU": "LUX", "LR": "LBR", "LS": "LSO", "TH": "THA", "TF": "ATF", "TG": "TGO", "TD": "TCD", "TC": "TCA", "LY": "LBY", "VA": "VAT", "VC": "VCT", "AE": "ARE", "AD": "AND", "AG": "ATG", "AF": "AFG", "AI": "AIA", "VI": "VIR", "IS": "ISL", "IR": "IRN", "AM": "ARM", "AL": "ALB", "AO": "AGO", "AN": "ANT", "AQ": "ATA", "AS": "ASM", "AR": "ARG", "AU": "AUS", "AT": "AUT", "AW": "ABW", "IN": "IND", "AX": "ALA", "AZ": "AZE", "IE": "IRL", "ID": "IDN", "UA": "UKR", "QA": "QAT", "MZ": "MOZ"},

    // iso alpha-3 --> continent code
    ISO3toCONT: {"AGO": "AF", "DZA": "AF", "EGY": "AF", "BGD": "AS", "NER": "AF", "LIE": "EU", "NAM": "AF", "BGR": "EU", "BOL": "SA", "GHA": "AF", "CCK": "AS", "PAK": "AS", "CPV": "AF", "JOR": "AS", "LBR": "AF", "LBY": "AF", "MYS": "OC", "DOM": "NA", "PRI": "NA", "SXM": "NA", "PRK": "AS", "PSE": "AS", "TZA": "AF", "BWA": "AF", "KHM": "AS", "UMI": "OC", "NIC": "NA", "TTO": "NA", "ETH": "AF", "PRY": "SA", "HKG": "AS", "SAU": "AS", "LBN": "AS", "SVN": "EU", "BFA": "AF", "CHE": "EU", "MRT": "AF", "HRV": "EU", "CHL": "SA", "CHN": "AS", "KNA": "NA", "SLE": "AF", "JAM": "NA", "SMR": "EU", "GIB": "EU", "DJI": "AF", "GIN": "AF", "FIN": "EU", "URY": "SA", "THA": "AS", "STP": "AF", "SYC": "AF", "NPL": "AS", "CXR": "AS", "LAO": "AS", "YEM": "AS", "BVT": "AN", "ZAF": "AF", "KIR": "OC", "PHL": "AS", "ROU": "EU", "VIR": "NA", "SYR": "AS", "MAC": "AS", "MAF": "NA", "MLT": "EU", "KAZ": "AS", "TCA": "NA", "PYF": "OC", "NIU": "OC", "DMA": "NA", "BEN": "AF", "GUF": "SA", "BEL": "EU", "MSR": "NA", "TGO": "AF", "DEU": "EU", "GUM": "OC", "LKA": "AS", "SSD": "AF", "FLK": "SA", "GBR": "EU", "BES": "NA", "GUY": "SA", "CRI": "NA", "CMR": "AF", "MAR": "AF", "MNP": "OC", "LSO": "AF", "HUN": "EU", "TKM": "AS", "SUR": "SA", "NLD": "EU", "BMU": "NA", "HMD": "AN", "TCD": "AF", "GEO": "AS", "MNE": "EU", "MNG": "AS", "MHL": "OC", "MTQ": "NA", "BLZ": "NA", "NFK": "OC", "MMR": "AS", "AFG": "AS", "BDI": "AF", "VGB": "NA", "BLR": "EU", "BLM": "NA", "GRD": "NA", "TKL": "OC", "GRC": "EU", "RUS": "EU", "GRL": "NA", "SHN": "AF", "AND": "EU", "MOZ": "AF", "TJK": "AS", "XKX": "EU", "HTI": "NA", "MEX": "NA", "ANT": "NA", "ZWE": "AF", "LCA": "NA", "IND": "AS", "LVA": "EU", "BTN": "AS", "VCT": "NA", "VNM": "AS", "NOR": "EU", "CZE": "EU", "ATF": "AN", "ATG": "NA", "FJI": "OC", "IOT": "AS", "HND": "NA", "MUS": "AF", "ATA": "AN", "LUX": "EU", "ISR": "AS", "FSM": "OC", "PER": "SA", "REU": "AF", "IDN": "OC", "VUT": "OC", "MKD": "EU", "COD": "AF", "COG": "AF", "ISL": "EU", "GLP": "NA", "COK": "OC", "COM": "AF", "COL": "SA", "NGA": "AF", "TLS": "OC", "TWN": "AS", "PRT": "EU", "MDA": "EU", "GGY": "EU", "MDG": "AF", "ECU": "SA", "SEN": "AF", "NZL": "OC", "MDV": "AS", "ASM": "OC", "SPM": "NA", "CUW": "NA", "FRA": "EU", "LTU": "EU", "RWA": "AF", "ZMB": "AF", "GMB": "AF", "WLF": "OC", "JEY": "EU", "FRO": "EU", "GTM": "NA", "DNK": "EU", "IMN": "EU", "AUS": "OC", "AUT": "EU", "SJM": "EU", "VEN": "SA", "PLW": "OC", "KEN": "AF", "MYT": "AF", "WSM": "OC", "TUR": "AS", "ALB": "EU", "OMN": "AS", "TUV": "OC", "ALA": "EU", "BRN": "AS", "TUN": "AF", "PCN": "OC", "BRB": "NA", "BRA": "SA", "CIV": "AF", "SRB": "EU", "GNQ": "AF", "USA": "NA", "QAT": "AS", "SWE": "EU", "AZE": "AS", "GNB": "AF", "SWZ": "AF", "TON": "OC", "CAN": "NA", "UKR": "EU", "KOR": "AS", "AIA": "NA", "CAF": "AF", "SVK": "EU", "CYP": "EU", "BIH": "EU", "SGP": "AS", "SGS": "AN", "SOM": "AF", "UZB": "AS", "ERI": "AF", "POL": "EU", "KWT": "AS", "SCG": "EU", "GAB": "AF", "CYM": "NA", "VAT": "EU", "EST": "EU", "MWI": "AF", "ESP": "EU", "IRQ": "AS", "SLV": "NA", "MLI": "AF", "IRL": "EU", "IRN": "AS", "ABW": "NA", "PNG": "OC", "PAN": "NA", "SDN": "AF", "SLB": "OC", "ESH": "AF", "MCO": "EU", "ITA": "EU", "JPN": "AS", "KGZ": "AS", "UGA": "AF", "NCL": "OC", "ARE": "AS", "ARG": "SA", "BHS": "NA", "BHR": "AS", "ARM": "AS", "NRU": "OC", "CUB": "NA"},

    // custom country label positions [lon, lat]
    customLabelPositions: {
        CZE: { DEU: [12.3, 49] },
        DEU: { AUT: [13.9, 48.1] },
        ESP: { PRT: [-8.5, 39.6] },
        NLD: { BEL: [4.6, 51, 1], DEU: [6.9, 51.5] },
        CHE: { FRA: [6.2, 47.2], AUT: [9.95, 47.2], ITA: [9.7, 46.0], DEU: [8.14, 47.83] },
        USA: { MEX: [-102, 24], CAN: [-97, 52] },
        BIH: { HRV: [15.3, 45] }
    },

    // mapping from Piwik continents to continents used in this widget
    cont2cont: {
        afr: 'AF', eur: 'EU', amn: 'NA', ams: 'SA', asi: 'AS', oce: 'OC', amc: 'SA'
    }

});


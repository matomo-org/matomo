// # UserCountryMap
// define a global scope
window.UserCountryMap = {};


UserCountryMap.run = function(config) {

    var map = $K.map('#UserCountryMap_map'),
        main = $('#UserCountryMap_container'),
        worldTotalVisits = 0,
        width = main.width();

    UserCountryMap.config = config;
    UserCountryMap.config.noDataColor = '#E4E2D7';
    UserCountryMap.widget = $('#widgetUserCountryMapworldMap').parent();

    window.__userCountryMap = map;

    function minmax(values) {
        values = values.sort(function(a,b) { return Number(a) - Number(b); });
        return {
            min: values[0],
            max: values[values.length-1],
            median: values[Math.floor(values.length*0.5)],
            p33: values[Math.floor(values.length*0.33)],
            p66: values[Math.floor(values.length*0.66)],
            p90: values[Math.floor(values.length*0.9)]
        };
    }

    /*
     * resizes the map
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

    function formatNumber(v) {
        v = Number(v);
        return v > 1000000 ? (v/1000000).toFixed(1) + 'm' :
            v > 1000 ? (v/1000).toFixed(1) + 'k' :
            v;
    }

    //
    // Since some metrics are transmitted in an non-numeric format like
    // "61.45%", we need to parse the numbers to make sure they can be
    // used for color scales etc. The parsed metrics will be stored as
    // METRIC_raw
    //

    function formatValueForTooltips(data, metric, id) {

        var val = data[metric] % 1 === 0 || Number(data[metric]) != data[metric]  ? data[metric] : data[metric].toFixed(1),
            v = UserCountryMap._[metric].replace('%s', '<b>'+val+'</b>');

        function avgTime(d) { return d['sum_visit_length'] / d['nb_visits']; }

        if (metric.substr(0, 3) == 'nb_' && metric != 'nb_actions_per_visit') {
            var total;
            if (id.length == 3) total = UserCountryMap.countriesByIso[id][metric];
            else if (id == 'world') total = UserCountryMap._worldTotal;
            else {
                total = 0;
                $.each(UserCountryMap.countriesByIso, function(iso, country) {
                    if (UserCountryMap.ISO3toCONT[iso] == id) {
                        total += country[metric];
                    }
                });
            }
            if (total) {
                v += ' ('+formatPercentage(data[metric] / total)+')';
            }
        } else if (metric == 'avg_time_on_site') {
            v += '<br/> (over '+data.nb_visits+' visits)';
        }
        return v;
    }

    function getColorScale(rows, metric, filter, choropleth) {
        var stats, values = [], id = UserCountryMap.lastSelected, c;

        $.each(rows, function(i, r) {
            if (!$.isFunction(filter) || filter(r)) {
                var v = quantify(r, metric);
                if (!isNaN(v)) values.push(v);
            }
        });

        stats = minmax(values);

        var colscale = new chroma.ColorScale({
            colors: [choropleth ? '#CDDAEF' : '#385993', '#385993'],
            limits: chroma.limits(values, 'c', 4),
            mode: 'hcl'
        });

        if (metric == 'avg_time_on_site' || metric == 'nb_actions_per_visit' || metric == 'bounce_rate') {
            if (id.length == 3) {
                c = (stats.p90 - stats.min) / (stats.max - stats.min);
                colscale = new chroma.ColorScale({
                    colors: ['#385993', '#385993','#E87500', '#E87500'],
                    limits: chroma.limits(rows, 'c', 5, 'curMetric', filter),
                    positions: [0, c, c+0.001, 1],
                    mode: 'hsl'
                });
            }
        }

        // a good place to update the legend, isn't it?
        if (choropleth) {
            $('.UserCountryMap-legend .content').html('').show();
            var b = function(val) {
                var d = $('<div>'), r = $('<div>'), l = $('<div>'), v = formatNumber(val);
                d.css({ width: 17, height: 17, float: 'left', background: colscale.getColor(val) });
                l.css({ 'margin-left':20, 'line-height': '20px', 'text-align': 'right' }).html(v);
                r.css({ clear: 'both', height: 19 });
                r.append(d).append(l);
                $('.UserCountryMap-legend .content').append(r);
            };
            $.each(chroma.limits(values, 'k', 3), function(i, v) {
                b(v);
            });

        } else {
            $('.UserCountryMap-legend .content').hide();
        }

        return colscale;
    }

    function showLegend(colscale, metric) {
        var lgd = $('#UserCountryMap-legend');

    }

    function formatPercentage(val) {
        if (val < 0.001) return '< 0.1%';
        return Math.round(1000 * val)/10 + '%';
    }

    /*
     * to ensure that onResize is not called a hundred times
     * while resizing the browser window, this functions
     * makes sure to only call onResize at the end
     */
    function onResizeLazy() {
        clearTimeout(UserCountryMap._resizeTimer);
        UserCountryMap._resizeTimer = setTimeout(onResize, 300);
    }

    function activateButton(btn) {
        $('#UserCountryMap-view-mode-buttons a').removeClass('activeIcon');
        btn.addClass('activeIcon');
        $('#UserCountryMap-activeItem').offset({ left: btn.offset().left });
    }

    function initUserInterface() {
        // react to changes of country select
        $('#userCountryMapSelectCountry').change(function() {
            updateState($('#userCountryMapSelectCountry').val());
        });

        function zoomOut() {
            var t = UserCountryMap.lastSelected,
                tgt = 'world';  // zoom out to world per default..
            if (t.length == 3 && UserCountryMap.ISO3toCONT[t] !== undefined) {
                tgt = UserCountryMap.ISO3toCONT[t];  // ..but zoom to continent if we know it
            }
            updateState(tgt);
        }

        // enable zoom-out
        $('#UserCountryMap-btn-zoom').click(zoomOut);
        $('#UserCountryMap_map').click(zoomOut);

        // handle window resizes
        $(window).resize(onResizeLazy);

        // enable mertic changes
        $('#userCountryMapSelectMetrics').change(function() {
            updateState(UserCountryMap.lastSelected);
        });

        // handle city button
        (function(btn) {
            btn.click(function() {
                if (UserCountryMap.lastSelected.length == 3) {
                    if (UserCountryMap.mode != "city") {
                        UserCountryMap.mode = "city";
                        updateState(UserCountryMap.lastSelected);
                    }
                }
            });
        })($('#UserCountryMap-btn-city'));

        // handle region button
        (function(btn) {
            btn.click(function() {
                if (UserCountryMap.mode != "region") {
                    $('#UserCountryMap-view-mode-buttons a').removeClass('activeIcon');
                    UserCountryMap.mode = "region";
                    updateState(UserCountryMap.lastSelected);
                }
            });
        })($('#UserCountryMap-btn-region'));

        // add loading indicator overlay
        var bl = $('<div id="UserCountryMap-black"></div>');
        bl.hide();
        $('#UserCountryMap_map').append(bl);
    }


    /*
     * updateState, called whenever the view changes
     */
    function updateState(id) {
        // double check view mode
        if (UserCountryMap.mode == "city" && id.length != 3) {
            // city mode is reserved for country views
            UserCountryMap.mode = "region";
        }

        var metric = $('#userCountryMapSelectMetrics').val();

        // store current map state
        UserCountryMap.widget.dashboardWidget('setParameters', {
            lastMap: id, viewMode: UserCountryMap.mode, lastMetric: metric
        });

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
        console.error(e);
    }

        _updateUI(id, metric);

        UserCountryMap.lastSelected = id;
    }

    /*
     * update the widgets ui according to the currently selected view
     */
    function _updateUI(id, metric) {
        // update UI
        if (UserCountryMap.mode == "city") {
            activateButton($('#UserCountryMap-btn-city'));
        } else {
            activateButton($('#UserCountryMap-btn-region'));
        }
        var countrySelect = $('#userCountryMapSelectCountry');
        countrySelect.val(id);

        var zoom = $('#UserCountryMap-btn-zoom');
        if (id == 'world') zoom.addClass('inactiveIcon');
        else zoom.removeClass('inactiveIcon');

        // show flag icon in select box
        var flag = $('#userCountryMapFlag'),
            regionBtn = $('#UserCountryMap-btn-region');
        if (id.length == 3) {
            flag.css({
                'background-image': 'url('+UserCountryMap.countriesByIso[id].flag+')',
                'background-repeat': 'no-repeat',
                'background-position': '5px 5px'
            });
            $('#UserCountryMap-btn-city').removeClass('inactiveIcon').show();
            $('span', regionBtn).html(regionBtn.data('region'));
        } else {
            flag.css({
                'background': 'none'
            });
            $('#UserCountryMap-btn-city').addClass('inactiveIcon').hide();
            $('span', regionBtn).html(regionBtn.data('country'));
        }

        var mapTitle = id.length == 3 ?
                UserCountryMap.countriesByIso[id].name :
                $('#userCountryMapSelectCountry option[value='+id+']').html(),
            totalVisits = 0;
        // update map title
        $('.map-title').html(mapTitle);
        // update total visits for that region
        if (id.length == 3) {
            totalVisits = UserCountryMap.countriesByIso[id]['nb_visits'];
        } else if (id.length == 2) {
            $.each(UserCountryMap.countriesByIso, function(iso, country) {
                if (UserCountryMap.ISO3toCONT[iso] == id) {
                    totalVisits += country['nb_visits'];
                }
            });
        } else {
            totalVisits = UserCountryMap.config.visitsSummary['nb_visits'];
        }

        if (id.length == 3) {
            $('.map-stats').html(formatValueForTooltips(UserCountryMap.countriesByIso[id], metric, 'world'));
        } else {
            $('.map-stats').html(UserCountryMap._.nb_visits.replace('%s', '<b>'+formatNumber(totalVisits) + '</b>') +(id != 'world' ? ' ('+
            formatPercentage(totalVisits / worldTotalVisits)+')' : ''));
        }
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
            colscale = getColorScale(UserCountryMap.countryData, metric, function(r) {
                if (target.length == 2) {
                    return UserCountryMap.ISO3toCONT[r.iso] == target;
                } else {
                    return true;
                }
            }, true);

            // Apply the color scale to the map.
            map.getLayer('countries').style('fill', function(data, path) {
                var d = UserCountryMap.countriesByIso[data.iso];
                if (d === null) {
                    return UserCountryMap.config.noDataColor;
                } else {
                    return colscale.getColor(d[metric]);
                }
            });

            // Update the map tooltips.
            map.getLayer('countries').tooltips(function(data) {
                var metric = $('#userCountryMapSelectMetrics').val(),
                    country = UserCountryMap.countriesByIso[data.iso];
                return '<h3>'+country.name + '</h3>'+
                    formatValueForTooltips(country, metric, target);
            });
        }

        // if the view hasn't changed (but probably the selected metric),
        // all we need to do is to recolor the current map.
        if (target == UserCountryMap.lastSelected) {
            updateColorsAndTooltips(metric);
            return;
        }

        // otherwise we need to load another map svg
        _updateMap(target + '.svg', function() {

            // add a layer for non-selectable countries = for which no data is
            // defined in the current report
            map.addLayer('countries', {
                name: 'context',
                filter: function(pd) {
                    return UserCountryMap.countriesByIso[pd.iso] === undefined;
                },
                tooltips: function(pd) {
                    return '<h3>'+pd.name+'</h3>No Visits';
                }
            });

            // add a layer for selectable countries = for which we have data
            // available in the current report
            map.addLayer('countries', { name: 'countryBG', filter: function(pd) {
                return UserCountryMap.countriesByIso[pd.iso] !== undefined;
            }});

            map.addLayer('countries', {
                key: 'iso',
                filter: function(pd) {
                    return UserCountryMap.countriesByIso[pd.iso] !== undefined;
                },
                click: function(data, path, evt) {
                    evt.stopPropagation();
                    var tgt;
                    if (UserCountryMap.lastSelected != 'world' || UserCountryMap.countriesByIso[data.iso] === undefined) {
                        tgt = data.iso;
                    } else {
                        tgt = UserCountryMap.ISO3toCONT[data.iso];
                    }
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
        map.loadMap(config.svgBasePath + svgUrl, function() {

            map.clear();
            onResize();
            callback();

            $('.ui-tooltip').remove(); // remove all existing tooltips

        }, { padding: -3});
    }

    function indicateLoading() {
        $('#UserCountryMap-black').show();
        $('#UserCountryMap-black').css('opacity', 0);
        $('#UserCountryMap-black').animate({ opacity: 0.5 }, 400);
        $('#UserCountryMap .loadingPiwik').show();
    }

    function loadingComplete() {
        $('#UserCountryMap-black').hide();
        $('#UserCountryMap .loadingPiwik').hide();
    }

    /*
     * returns a quantifiable value for a given metric
     */
    function quantify(d, metric) {
        if (!metric) metric = $('#userCountryMapSelectMetrics').val();
        switch (metric) {
            case 'avg_time_on_site':
                return d.sum_visit_length / d.nb_visits;
            case 'bounce_rate':
                return d.bounce_count / d.nb_visits;
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
        $.each(rows, function(i, row) {
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
                $.each(groups[g_id], function(metric) {
                    groups[g_id][metric] += row[metric];
                });
            }
        });

        $.each(groups, function(g_id, group) {
            var apv = group.nb_actions / group.nb_visits,
                ats = group.sum_visit_length / group.nb_visits,
                br = (group.bounce_count * 100 / group.bounce_count);
            group['nb_actions_per_visit'] = apv;
            group['avg_time_on_site'] = new Date(0,0,0,ats / 3600, ats % 3600 / 60, ats % 60).toLocaleTimeString();
            group['bounce_rate'] = (br % 1 !== 0 ? br.toFixed(1) : br)+"%";
        });

        return groupBy ? groups : groups.X;
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
            $.ajax({
                url: config.regionDataUrl + UserCountryMap.countriesByIso[iso].iso2,
                dataType: 'json',
                success : function(data) {

                    loadingComplete();

                    var regionDict = {};
                    // UserCountryMap.lastReportMetricStats = {};

                    $.each(data.reportData, function(i, row) {
                        regionDict[data.reportMetadata[i].region] = $.extend(row, data.reportMetadata[i], {
                            curMetric: quantify(row, metric)
                        });
                    });

                    var metric = $('#userCountryMapSelectMetrics').val();

                    function regionCode(region) {
                        var iso = UserCountryMap.lastSelected,
                            useFips2 = iso == "ESP" || iso == "BEL" || iso == "GBR",
                            usePostal = iso == "USA" || iso == "CAN";
                        if (useFips2) {
                            return region['fips-'].substr(2);
                        } else if (usePostal) {
                            return region.p;
                        }
                        return region.fips.substr(2);  // cut first two letters from fips code (=country code)
                    }

                    if (UserCountryMap.aggregate[iso]) {
                        var aggregated = aggregate(regionDict, function(row) {
                            var id = row.region, res = false;
                            $.each(UserCountryMap.aggregate[iso].groups, function(group, codes) {
                                if ($.inArray(id, codes) > -1) {
                                    res = group;
                                }
                            });
                            return res;
                        });
                        $.each(aggregated, function(id, group) {
                            group.curMetric = quantify(group, metric);
                            regionDict[id] = group;
                        });
                    }

                    // create color scale
                    colscale = getColorScale(regionDict, 'curMetric', null, true);

                    // apply colors to map
                    map.getLayer('regions').style('fill', function(data, path) {
                        var code = regionCode(data);
                        return regionDict[code] === undefined ? '#fff' : colscale.getColor(regionDict[code].curMetric);
                    }).style('stroke', function(data) {
                        return regionDict[regionCode(data)] === undefined ? '#bbb' : '#3C6FB6';
                    });
                    // add tooltips for regions
                    map.getLayer('regions').tooltips(function(data) {
                        var metric = $('#userCountryMapSelectMetrics').val(),
                        region = regionDict[regionCode(data)];
                        if (region === undefined) {
                            return '<h3>'+data.name+'</h3><p>'+UserCountryMap._.nb_visits.replace('%s', '<b>0</b>')+'</p>';
                        }
                        return '<h3>'+data.name+'</h3>'+
                            formatValueForTooltips(region, metric, iso);
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
            if (map.getLayer('regions')) map.getLayer('regions').style('fill', '#fff');

            indicateLoading();

            // get visits per city from API
            $.ajax({
                url: config.cityDataUrl + UserCountryMap.countriesByIso[iso].iso2,
                dataType: 'json',
                success : function(data) {

                    loadingComplete();

                    var metric = $('#userCountryMapSelectMetrics').val(),
                        colscale,
                        unknown,
                        cities = [];

                    // merge reportData and reportMetadata to cities array
                    $.each(data.reportData, function(i, row) {
                        cities.push($.extend(row, data.reportMetadata[i], {
                            curMetric: quantify(row, metric)
                        }));
                    });

                    // get metric for visits with unknown city
                    unknown = aggregate(cities, function(row) {
                        return row.city == "xx" ? 'unknown' : false;
                    }).unknown;

                    // sort by current metric
                    cities.sort(function(a, b) { return b.curMetric - a.curMetric; });

                    colscale = getColorScale(cities, metric);

                    // construct scale
                    var radscale = $K.scale.linear(cities.concat({ curMetric: 0 }), 'curMetric');

                    var area = map.container.width() * map.container.height(),
                        sumArea = 0,
                        f = {
                            nb_visits: 0.002,
                            nb_actions: 0.002,
                            avg_time_on_site: 0.02,
                            nb_actions_per_visit: 0.02,
                            bounce_rate: 0.02
                        },
                        maxRad;

                    $.each(cities, function(i, city) {
                        sumArea += isNaN(city.curMetric) ? 0 : Math.pow(radscale(city.curMetric), 2);
                    });
                    maxRad = Math.sqrt(area * f[metric] / sumArea);

                    radscale = $K.scale.sqrt(cities.concat({ curMetric: 0 }), 'curMetric').range([2, maxRad+2]);

                    var is_rate = metric.substr(0,3) != 'nb_' || metric == 'nb_actions_per_visit';

                    map.addSymbols({
                        type: $K.LabeledBubble,
                        data: cities,
                        clustering: 'noverlap',
                        clusteringOpts: {
                            size: 128,
                            tolerance: 0
                        },
                        title: function(d) {
                            return radscale(d.curMetric) > 10 ? formatNumber(d.curMetric) : '';
                        },
                        labelattrs: {
                            fill: '#fff',
                            'font-size': 11,
                            stroke: false
                        },
                        filter: function(d) {
                            return is_rate ? d.nb_visits > 5 && d.curMetric : d.curMetric;
                        },
                        aggregate: function(rows) {
                            var row = aggregate(rows);
                            row.city_names = [];
                            $.each(rows, function(i, r) {
                                row.city_names = row.city_names.concat(r.city_names ? r.city_names : [r.city_name]);
                            });
                            row.city_name = row.city_names[0] + (row.city_names.length > 1 ? ' '+UserCountryMap._.and_n_others.replace('%s', (row.city_names.length-1)) : '');
                            row.curMetric = quantify(row, metric);
                            return row;
                        },
                        sortBy: 'radius desc',
                        location: function(city) { return [city.long, city.lat]; },
                        radius: function(city) { return radscale(city.curMetric); },
                        tooltip: function(city) {
                            return '<h3>'+city.city_name+'</h3>'+
                                formatValueForTooltips(city, metric, iso);
                        },
                        attrs: function(city) {
                            return {
                                fill: colscale.getColor(city.curMetric),
                                'fill-opacity': 0.7,
                                stroke: '#fff'
                            };
                        },
                        mouseenter: function(city, symbol) {
                            symbol.path.attr({
                                'fill-opacity': 1,
                                'stroke': '#000000',
                                'stroke-opacity': 1,
                                'stroke-width': 2
                            });
                        },
                        mouseleave: function(city, symbol) {
                            symbol.path.attr({
                                'fill-opacity': 0.7,
                                'stroke-opacity': 1,
                                'stroke-width': 1,
                                'stroke': '#ffffff'
                            });
                        },
                        click: function() {}
                    });
                }
            });
        }


        _updateMap(iso + '.svg', function() {
            // add background
            map.addLayer('context', {
                key: 'iso',
                filter: function(pd) {
                    return UserCountryMap.countriesByIso[pd.iso] === undefined;
                }
            });
            map.addLayer('context', {
                key: 'iso',
                name: 'context-clickable',
                filter: function(pd) {
                    return UserCountryMap.countriesByIso[pd.iso] !== undefined;
                },
                click: function(path, p, evt) {   // add click events for surrounding countries
                    evt.stopPropagation();
                    updateState(path.iso);
                },
                tooltips: function(data) {
                    if (UserCountryMap.countriesByIso[data.iso] === undefined) {
                        return 'no data';
                    }
                    var metric = $('#userCountryMapSelectMetrics').val(),
                        country = UserCountryMap.countriesByIso[data.iso];
                    return '<h3>'+country.name+'</h3>'+
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
                key: 'fips',
                name: UserCountryMap.mode != "region" ? "regions2" : "regions",
                click: function(d, p, evt) {
                    evt.stopPropagation();
                }
            });
             function filtCountryLabels(data) {
                return data.iso != iso && Math.abs(map.getLayer('context-clickable').getPath(data.iso).path.area()) > 700;
            }
            map.addSymbols({
                data: map.getLayer('context-clickable').getPathsData(),
                type: $K.Label,
                filter: filtCountryLabels,
                location: function(data) { return 'context-clickable.'+data.iso; },
                text: function(data) { return UserCountryMap.countriesByIso[data.iso].iso2; },
                'class': 'countryLabelBg'
            });
            map.addSymbols({
                data: map.getLayer('context-clickable').getPathsData(),
                type: $K.Label,
                filter: filtCountryLabels,
                location: function(data) { return 'context-clickable.'+data.iso; },
                text: function(data) { return UserCountryMap.countriesByIso[data.iso].iso2; },
                'class': 'countryLabel'
            });

            if (UserCountryMap.mode == "region") {
                updateRegionColors();
            } else {
                updateCitySymbols();
            }

        });
    }

    // now load the metrics for all countries
    $.getJSON(config.countryDataUrl, function(report) {

        var metrics = $('#userCountryMapSelectMetrics option');
        var countryData = [], countrySelect = $('#userCountryMapSelectCountry'),
            countriesByIso = {};
        UserCountryMap.lastReportMetricStats = {};
        // read api result to countryData and countriesByIso
        $.each(report.reportData, function(i, data) {
            var meta = report.reportMetadata[i],
                country = {
                    name: data.label,
                    iso2: meta.code.toUpperCase(),
                    iso: UserCountryMap.ISO2toISO3[meta.code.toUpperCase()],
                    flag: meta.logo
                };
            $.each(metrics, function(i, metric) {
                metric = $(metric).attr('value');
                country[metric] = data[metric];
            });
            countryData.push(country);
            countriesByIso[country.iso] = country;
            worldTotalVisits += country['nb_visits'];
        });
        UserCountryMap._worldTotal = worldTotalVisits;
        // sort countries by name
        countryData.sort(function(a,b) { return a.name > b.name ? 1 : -1; });

        // store country data globally
        UserCountryMap.countryData = countryData;
        UserCountryMap.countriesByIso = countriesByIso;

        map.loadCSS(config.mapCssPath, function() {
            // map stylesheets are loaded

            // hide loading indicator
            $('#widgetUserCountryMapworldMap .loadingPiwik').hide();

            // start with default view (or saved state??)
            var params = UserCountryMap.widget.dashboardWidget('getWidgetObject').parameters;
            UserCountryMap.mode = params && params.viewMode ? params.viewMode : 'region';
            if (params && params.lastMetric) $('#userCountryMapSelectMetrics').val(params.lastMetric);
            updateState(params && params.lastMap ? params.lastMap : 'world');

            // populate country select
            $.each(countryData, function(i, country) {
                countrySelect.append('<option value="'+country.iso+'">'+country.name+'</option>');
            });

            initUserInterface();

        });
    });


    $('.UserCountryMap-overlay').on('mouseenter', function(e) {
        $('.content', $(e.target).parents('.UserCountryMap-overlay')).fadeOut(200);
        setTimeout(function() {
           $('.UserCountryMap-overlay .content').fadeIn(1000);
        }, 3000);
    });

};


UserCountryMap.ISO2toISO3 = {"BD": "BGD", "BE": "BEL", "BF": "BFA", "BG": "BGR", "BA": "BIH", "BB": "BRB", "WF": "WLF", "BL": "BLM", "BM": "BMU", "BN": "BRN", "BO": "BOL", "BH": "BHR", "BI": "BDI", "BJ": "BEN", "BT": "BTN", "JM": "JAM", "BV": "BVT", "BW": "BWA", "WS": "WSM", "BQ": "BES", "BR": "BRA", "BS": "BHS", "JE": "JEY", "BY": "BLR", "BZ": "BLZ", "RU": "RUS", "RW": "RWA", "RS": "SRB", "TL": "TLS", "RE": "REU", "TM": "TKM", "TJ": "TJK", "RO": "ROU", "TK": "TKL", "GW": "GNB", "GU": "GUM", "GT": "GTM", "GS": "SGS", "GR": "GRC", "GQ": "GNQ", "GP": "GLP", "JP": "JPN", "GY": "GUY", "GG": "GGY", "GF": "GUF", "GE": "GEO", "GD": "GRD", "GB": "GBR", "GA": "GAB", "SV": "SLV", "GN": "GIN", "GM": "GMB", "GL": "GRL", "GI": "GIB", "GH": "GHA", "OM": "OMN", "TN": "TUN", "JO": "JOR", "HR": "HRV", "HT": "HTI", "HU": "HUN", "HK": "HKG", "HN": "HND", "HM": "HMD", "VE": "VEN", "PR": "PRI", "PS": "PSE", "PW": "PLW", "PT": "PRT", "SJ": "SJM", "PY": "PRY", "IQ": "IRQ", "PA": "PAN", "PF": "PYF", "PG": "PNG", "PE": "PER", "PK": "PAK", "PH": "PHL", "PN": "PCN", "PL": "POL", "PM": "SPM", "ZM": "ZMB", "EH": "ESH", "EE": "EST", "EG": "EGY", "ZA": "ZAF", "EC": "ECU", "IT": "ITA", "VN": "VNM", "SB": "SLB", "ET": "ETH", "SO": "SOM", "ZW": "ZWE", "SA": "SAU", "ES": "ESP", "ER": "ERI", "ME": "MNE", "MD": "MDA", "MG": "MDG", "MF": "MAF", "MA": "MAR", "MC": "MCO", "UZ": "UZB", "MM": "MMR", "ML": "MLI", "MO": "MAC", "MN": "MNG", "MH": "MHL", "MK": "MKD", "MU": "MUS", "MT": "MLT", "MW": "MWI", "MV": "MDV", "MQ": "MTQ", "MP": "MNP", "MS": "MSR", "MR": "MRT", "IM": "IMN", "UG": "UGA", "TZ": "TZA", "MY": "MYS", "MX": "MEX", "IL": "ISR", "FR": "FRA", "IO": "IOT", "SH": "SHN", "FI": "FIN", "FJ": "FJI", "FK": "FLK", "FM": "FSM", "FO": "FRO", "NI": "NIC", "NL": "NLD", "NO": "NOR", "NA": "NAM", "VU": "VUT", "NC": "NCL", "NE": "NER", "NF": "NFK", "NG": "NGA", "NZ": "NZL", "NP": "NPL", "NR": "NRU", "NU": "NIU", "CK": "COK", "XK": "XKX", "CI": "CIV", "CH": "CHE", "CO": "COL", "CN": "CHN", "CM": "CMR", "CL": "CHL", "CC": "CCK", "CA": "CAN", "CG": "COG", "CF": "CAF", "CD": "COD", "CZ": "CZE", "CY": "CYP", "CX": "CXR", "CS": "SCG", "CR": "CRI", "CW": "CUW", "CV": "CPV", "CU": "CUB", "SZ": "SWZ", "SY": "SYR", "SX": "SXM", "KG": "KGZ", "KE": "KEN", "SS": "SSD", "SR": "SUR", "KI": "KIR", "KH": "KHM", "KN": "KNA", "KM": "COM", "ST": "STP", "SK": "SVK", "KR": "KOR", "SI": "SVN", "KP": "PRK", "KW": "KWT", "SN": "SEN", "SM": "SMR", "SL": "SLE", "SC": "SYC", "KZ": "KAZ", "KY": "CYM", "SG": "SGP", "SE": "SWE", "SD": "SDN", "DO": "DOM", "DM": "DMA", "DJ": "DJI", "DK": "DNK", "VG": "VGB", "DE": "DEU", "YE": "YEM", "DZ": "DZA", "US": "USA", "UY": "URY", "YT": "MYT", "UM": "UMI", "LB": "LBN", "LC": "LCA", "LA": "LAO", "TV": "TUV", "TW": "TWN", "TT": "TTO", "TR": "TUR", "LK": "LKA", "LI": "LIE", "LV": "LVA", "TO": "TON", "LT": "LTU", "LU": "LUX", "LR": "LBR", "LS": "LSO", "TH": "THA", "TF": "ATF", "TG": "TGO", "TD": "TCD", "TC": "TCA", "LY": "LBY", "VA": "VAT", "VC": "VCT", "AE": "ARE", "AD": "AND", "AG": "ATG", "AF": "AFG", "AI": "AIA", "VI": "VIR", "IS": "ISL", "IR": "IRN", "AM": "ARM", "AL": "ALB", "AO": "AGO", "AN": "ANT", "AQ": "ATA", "AS": "ASM", "AR": "ARG", "AU": "AUS", "AT": "AUT", "AW": "ABW", "IN": "IND", "AX": "ALA", "AZ": "AZE", "IE": "IRL", "ID": "IDN", "UA": "R", "QA": "QAT", "MZ": "MOZ"};
UserCountryMap.ISO3toCONT = {"AGO": "AF", "DZA": "AF", "EGY": "AF", "BGD": "AS", "NER": "AF", "LIE": "EU", "NAM": "AF", "BGR": "EU", "BOL": "SA", "GHA": "AF", "CCK": "AS", "PAK": "AS", "CPV": "AF", "JOR": "AS", "LBR": "AF", "LBY": "AF", "MYS": "AS", "DOM": "NA", "PRI": "NA", "SXM": "NA", "PRK": "AS", "PSE": "AS", "TZA": "AF", "BWA": "AF", "KHM": "AS", "UMI": "OC", "NIC": "NA", "TTO": "NA", "ETH": "AF", "PRY": "SA", "HKG": "AS", "SAU": "AS", "LBN": "AS", "SVN": "EU", "BFA": "AF", "CHE": "EU", "MRT": "AF", "HRV": "EU", "CHL": "SA", "CHN": "AS", "KNA": "NA", "SLE": "AF", "JAM": "NA", "SMR": "EU", "GIB": "EU", "DJI": "AF", "GIN": "AF", "FIN": "EU", "URY": "SA", "THA": "AS", "STP": "AF", "SYC": "AF", "NPL": "AS", "CXR": "AS", "LAO": "AS", "YEM": "AS", "BVT": "AN", "ZAF": "AF", "KIR": "OC", "PHL": "AS", "ROU": "EU", "VIR": "NA", "SYR": "AS", "MAC": "AS", "MAF": "NA", "MLT": "EU", "KAZ": "AS", "TCA": "NA", "PYF": "OC", "NIU": "OC", "DMA": "NA", "BEN": "AF", "GUF": "SA", "BEL": "EU", "MSR": "NA", "TGO": "AF", "DEU": "EU", "GUM": "OC", "LKA": "AS", "SSD": "AF", "FLK": "SA", "GBR": "EU", "BES": "NA", "GUY": "SA", "CRI": "NA", "CMR": "AF", "MAR": "AF", "MNP": "OC", "LSO": "AF", "HUN": "EU", "TKM": "AS", "SUR": "SA", "NLD": "EU", "BMU": "NA", "HMD": "AN", "TCD": "AF", "GEO": "AS", "MNE": "EU", "MNG": "AS", "MHL": "OC", "MTQ": "NA", "BLZ": "NA", "NFK": "OC", "MMR": "AS", "AFG": "AS", "BDI": "AF", "VGB": "NA", "BLR": "EU", "BLM": "NA", "GRD": "NA", "TKL": "OC", "GRC": "EU", "RUS": "EU", "GRL": "NA", "SHN": "AF", "AND": "EU", "MOZ": "AF", "TJK": "AS", "XKX": "EU", "HTI": "NA", "MEX": "NA", "ANT": "NA", "ZWE": "AF", "LCA": "NA", "IND": "AS", "LVA": "EU", "BTN": "AS", "VCT": "NA", "VNM": "AS", "NOR": "EU", "CZE": "EU", "ATF": "AN", "ATG": "NA", "FJI": "OC", "IOT": "AS", "HND": "NA", "MUS": "AF", "ATA": "AN", "LUX": "EU", "ISR": "AS", "FSM": "OC", "PER": "SA", "REU": "AF", "IDN": "AS", "VUT": "OC", "MKD": "EU", "COD": "AF", "COG": "AF", "ISL": "EU", "GLP": "NA", "COK": "OC", "COM": "AF", "COL": "SA", "NGA": "AF", "TLS": "OC", "TWN": "AS", "PRT": "EU", "MDA": "EU", "GGY": "EU", "MDG": "AF", "ECU": "SA", "SEN": "AF", "NZL": "OC", "MDV": "AS", "ASM": "OC", "SPM": "NA", "CUW": "NA", "FRA": "EU", "LTU": "EU", "RWA": "AF", "ZMB": "AF", "GMB": "AF", "WLF": "OC", "JEY": "EU", "FRO": "EU", "GTM": "NA", "DNK": "EU", "IMN": "EU", "AUS": "OC", "AUT": "EU", "SJM": "EU", "VEN": "SA", "PLW": "OC", "KEN": "AF", "MYT": "AF", "WSM": "OC", "TUR": "AS", "ALB": "EU", "OMN": "AS", "TUV": "OC", "ALA": "EU", "BRN": "AS", "TUN": "AF", "PCN": "OC", "BRB": "NA", "BRA": "SA", "CIV": "AF", "SRB": "EU", "GNQ": "AF", "USA": "NA", "QAT": "AS", "SWE": "EU", "AZE": "AS", "GNB": "AF", "SWZ": "AF", "TON": "OC", "CAN": "NA", "R": "EU", "KOR": "AS", "AIA": "NA", "CAF": "AF", "SVK": "EU", "CYP": "EU", "BIH": "EU", "SGP": "AS", "SGS": "AN", "SOM": "AF", "UZB": "AS", "ERI": "AF", "POL": "EU", "KWT": "AS", "SCG": "EU", "GAB": "AF", "CYM": "NA", "VAT": "EU", "EST": "EU", "MWI": "AF", "ESP": "EU", "IRQ": "AS", "SLV": "NA", "MLI": "AF", "IRL": "EU", "IRN": "AS", "ABW": "NA", "PNG": "OC", "PAN": "NA", "SDN": "AF", "SLB": "OC", "ESH": "AF", "MCO": "EU", "ITA": "EU", "JPN": "AS", "KGZ": "AS", "UGA": "AF", "NCL": "OC", "ARE": "AS", "ARG": "SA", "BHS": "NA", "BHR": "AS", "ARM": "AS", "NRU": "OC", "CUB": "NA"};
UserCountryMap.aggregate = {
    "GBR": {
        "groups": {
            "SE": ["F2", "M4", "I6", "B9", "B6", "E2", "I3", "P6", "K2", "N7", "G2", "K6", "G5"],
            "WM": ["O2", "P3", "F7", "Q4", "N1", "N4", "L6"],
            "NI": ["R3", "S6", "T3", "Q8", "S9", "R2", "R8", "S1", "S5", "R7", "Q6", "S7", "Q9", "S3", "R4", "T1", "T2", "R9", "R6", "R1", "S4", "R5", "T4", "S2", "Q7", "S8"],
            "SW": ["E6", "A4", "B7", "J4", "M6", "M3", "D4", "B2", "D6", "K5", "C6", "K4", "O4", "N9", "P8"],
            "NW": ["E9", "C5", "A8", "H2", "C9", "P2", "I2"],
            "SL": ["U4", "U5", "U7", "V2", "V4", "U5", "V8", "W2", "W4", "W5", "W7", "T5", "T6", "V6", "V9", "W6", "U1", "W1", "T7", "U3", "V1", "U6", "U8", "V5", "W9", "T9", "U2", "U9", "V3", "T8", "W3", "W8"],
            "EM": ["H5", "D2", "D3", "H7", "J1", "H4", "L4", "J8", "J9"],
            "NE": ["", "D1", "D8", "F5", "I5", "K9", "N3", "J6"],
            "Ldn": ["A1", "A6", "B5", "B8", "C4", "C8", "D9", "E3", "E7", "F1", "G1", "G3", "G4", "I4", "K8", "L1", "N8", "O5", "O9", "P1", "P5", "F6", "F9", "G7", "E8", "F3", "F4", "H1", "H6", "I8", "M8"],
            "Wa": ["X7", "X6", "Y7", "Y8", "X3", "X4", "Y3", "Y9", "X5", "Z3", "Y5", "Z1", "X9", "Y1", "Z4", "X1", "X8", "Y2", "X2", "Y4", "Y6", "Z2"],
            "YH": ["G6", "J2", "J3", "Q5", "E1", "J7"],
            "E": ["A5", "F8", "C3", "E4", "N5", "I9", "O3", "I1", "K3"]
        }
    }
};
// # UserCountryMap
// define a global scope
window.UserCountryMap = {};

piwikHelper.getProcessedMetrics = function(row) {
    var avgTime = Math.round(row.sum_visit_length / row.nb_visits),
        avgH = Math.floor(avgTime / 3600),
        avgM = Math.floor((avgTime - avgH * 60) / 60),
        avgS = avgTime - avgH * 3600 - avgM * 60;

    row.processed = {
        nb_visits: row.nb_visits,
        nb_actions: row.nb_actions,
        nb_actions_per_visit: (row.nb_actions / row.nb_visits).toFixed(2),
        avg_time_on_site: ("0" + avgH).slice(-2) + ":" + ("0" + avgM).slice(-2) + ":" + ("0" + avgS).slice(-2),
        bounce_rate: (row.bounce_count / row.nb_visits).toFixed(2)+"%"
    };
};

UserCountryMap.run = function(config) {

    var map = $K.map('#UserCountryMap_map'),
        main = $('#UserCountryMap_container'),
        worldTotalVisits = 0,
        width = main.width();

    UserCountryMap.config = config;
    UserCountryMap.config.noDataColor = '#E4E2D7';
    UserCountryMap.widget = $('#widgetUserCountryMapworldMap').parent();

    window.__userCountryMap = map;

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
    }

    //
    // Since some metrics are transmitted in an non-numeric format like
    // "61.45%", we need to parse the numbers to make sure they can be
    // used for color scales etc. The parsed metrics will be stored as
    // METRIC_raw
    //
    function parseMetrics(data) {
        $.each(UserCountryMap.config.metrics, function(metric) {
            if (!data[metric]) return;
            var val, t, metricStats = UserCountryMap.lastReportMetricStats;
            if (metric == 'avg_time_on_site') {
                t = data[metric].split(":").map(Number);
                val = t[2] + t[1] * 60 + t[0] * 3600;
            } else if (metric == 'bounce_rate') {
                val = Number(data[metric].substr(0, data[metric].length-1));
            } else {
                val = data[metric];
            }
            if (metricStats[metric] === undefined) {
                metricStats[metric] = { sum: 0, min: Number.MAX_VALUE, max: Number.MIN_VALUE };
            }
            metricStats[metric].sum += val;
            metricStats[metric].min = Math.min(val, metricStats[metric].min);
            metricStats[metric].max = Math.max(val, metricStats[metric].max);
            data[metric+'_raw'] = val;
        });
    }

    function formatValueForTooltips(data, metric, id) {
        var v = '<b>'+data[metric] + '</b>';

        if (metric.substr(0, 3) == 'nb_' && metric != 'nb_actions_per_visit') {
            var total;
            if (id.length == 3) total = UserCountryMap.countriesByIso[id][metric+'_raw'];
            else total = UserCountryMap.lastReportMetricStats[metric].sum;
            v += ' ('+formatPercentage(data[metric+'_raw'] / total)+')';
        } else if (metric == 'avg_time_on_site') {
            var diff = data[metric+'_raw'] - UserCountryMap.config.visitsSummary[metric],
                neg = diff < 0,
                t;
            diff = Math.abs(diff);
            t = [Math.floor(diff/3600), Math.floor(diff/60) % 60, diff % 60];
            // t.map(function(s) { return s < 10 ? "0"+s : ""+s; }).join(':')
            v += ' ('+(neg ? '-' : '+')+'' + (t[0] > 0 ? t[0]+'h ' : '') + (t[1] > 0 ? t[1]+'m ' : '')+t[2]+'s)';
        }
        return UserCountryMap.config.metrics[metric]+': '+v;
    }

    function getColorScale(rows, metric, filter) {
        var stats = UserCountryMap.lastReportMetricStats[metric],
            avg = UserCountryMap.config.visitsSummary[metric];

        if (metric == 'avg_time_on_site') {
            // use diverging color scale for avg time on site
            console.info(rows[0][metric+'_raw'], stats.max, avg);
            return window.colscl = new chroma.ColorScale({
                colors: ['#9F454B', '#ffffff', '#8BABDF'],
                limits: chroma.limits(rows, 'c', 5, metric+'_raw', filter),
                positions: [0, (avg - 0) / (stats.max - 0), 1],
                mode: 'hcl'
            });

        } else if (metric == 'bounce_rate') {
            avg = Number(avg.substr(0, avg.length-1));
            // use diverging color scale for avg time on site
            return new chroma.ColorScale({
                colors: ['#8BABDF', '#ffffff', '#9F595F'],
                limits: chroma.limits(rows, 'c', 5, metric+'_raw', filter),
                positions: [0, (avg - stats.min) / (stats.max - stats.min), 1],
                mode: 'hcl'
            });

        } else {
            // default blue color scale
            return new chroma.ColorScale({
                colors: ['#CDDAEF', '#385993'],
                limits: chroma.limits(rows, 'e', 5, metric+'_raw', filter),
                mode: 'hcl'
            });
        }
    }

    function showLegend(colscale, metric) {
        var lgd = $('#UserCountryMap-legend');

    }

    function formatPercentage(val) {
        return Math.round(1000 * val)/10 + '%';
    }

    function formatNumber(val) {
        if (val > 5000) {
            return (Math.round(val / 100) / 10) +'k';
        } else {
            return val;
        }
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
     * updateState
     */
    function updateState(id) {
        // double check view mode
        if (UserCountryMap.mode == "city" && id.length != 3) {
            // city mode is reserved for country views
            UserCountryMap.mode = "region";
        }

        var metric = $('#userCountryMapSelectMetrics').val();

        // store map state
        UserCountryMap.widget.dashboardWidget('setParameters', {
            lastMap: id, viewMode: UserCountryMap.mode, lastMetric: metric
        });

        if (id.length == 3) {
            renderCountryMap(id, metric);
        } else {
            renderWorldMap(id, metric);
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
            $('#UserCountryMap-btn-city').removeClass('inactiveIcon');
            $('span', regionBtn).html(regionBtn.data('region'));
        } else {
            flag.css({
                'background': 'none'
            });
            $('#UserCountryMap-btn-city').addClass('inactiveIcon');
            $('span', regionBtn).html(regionBtn.data('country'));
        }

        var mapTitle = id.length == 3 ? UserCountryMap.countriesByIso[id].name : $('#userCountryMapSelectCountry option[value='+id+']').html(),
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
            $('.map-stats').html(UserCountryMap.config.metrics['nb_visits']+
            ': <b>'+formatNumber(totalVisits) + '</b>' +(id != 'world' ? ' ('+
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
            });

            // Apply the color scale to the map.
            map.getLayer('countries').style('fill', function(data, path) {
                var d = UserCountryMap.countriesByIso[data.iso];
                if (d === null) {
                    return UserCountryMap.config.noDataColor;
                } else {
                    return colscale.getColor(d[metric+'_raw']);
                }
            });

            // Update the map tooltips.
            map.getLayer('countries').tooltips(function(data) {
                var metric = $('#userCountryMapSelectMetrics').val(),
                    country = UserCountryMap.countriesByIso[data.iso];
                return '<h3>'+country.name + '</h3>'+formatValueForTooltips(country, metric, target);
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
            map.addLayer('countries', { name: 'context', filter: function(pd) {
                return UserCountryMap.countriesByIso[pd.iso] === undefined;
            }});

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
        $('#UserCountryMap-black').animate({ opacity: 0.3 }, 400);
        $('#UserCountryMap .loadingPiwik').show();
    }

    function loadingComplete() {
        $('#UserCountryMap-black').hide();
        $('#UserCountryMap .loadingPiwik').hide();
    }

    function renderCountryMap(iso) {

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
                        parseMetrics(row);
                        regionDict[data.reportMetadata[i].region] = row;
                    });

                    var metric = $('#userCountryMapSelectMetrics').val();
                    // create color scale
                    colscale = getColorScale(data.reportData, metric);

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

                    // apply colors to map
                    map.getLayer('regions').style('fill', function(data) {
                        var code = regionCode(data);
                        if (regionDict[code] === undefined) {
                            // not found :(
                            return UserCountryMap.config.noDataColor;
                        } else {
                            // match
                            return colscale.getColor(regionDict[code][metric+'_raw']);
                        }
                    });

                    // add tooltips for regions
                    map.getLayer('regions').tooltips(function(data) {
                        var metric = $('#userCountryMapSelectMetrics').val(),
                        region = regionDict[regionCode(data)];
                        if (region === undefined) {
                            return '<h3>'+data.name+'</h3><p>'+_pk_translate('General_NoVisits_js')+'</p>';
                        }
                        return '<h3>'+data.name+'</h3>'+
                            formatValueForTooltips(region, metric, iso);
                    });
                }
            });
        }

        function updateCitySymbols() {
            // load some fake data with real cities ids from GeoIP

            // color regions in light blue
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
                        cities = [],
                        unknown = 0,
                        cluster = kmeans().iterations(16).size(100);

                    $.each(data.reportData, function(i, row) {
                        parseMetrics(row);
                        if (data.reportMetadata[i].region == 'xx') {
                            // not safe to sum at this point (e.g. avg time on page)
                            unknown += row[metric+'_raw'];
                            //return;
                        }
                        cities.push($.extend(row, data.reportMetadata[i]));
                    });

                    cities.sort(function(a, b) { return b[metric+'_raw'] - a[metric+'_raw']; });
                    cities = cities.slice(0, 200);

                    colscale = getColorScale(cities, metric);

                    // construct scale
                    var scale = $K.scale.linear(cities, metric+'_raw');

                    var s = 0;
                    $.each(cities, function(i, city) {
                        s += city.size;
                    });
                    s /= cities.length;
                    var maxRad = 20;

                    map.addSymbols({
                        type: $K.Bubble,
                        data: cities,
                        location: function(city) { return [city.long, city.lat]; },
                        radius: function(city) { return scale(city[metric+'_raw']) * maxRad + 3; },
                        style: function(city) {
                            return 'fill:'+colscale.getColor(city[metric+'_raw']);
                        },
                        tooltip: function(city) {
                            return '<h3>'+city.city_name+'</h3>'+
                                formatValueForTooltips(city, metric, iso);
                        },
                        click: function(e) {
                            evt.stopPropagation();
                        }
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
            map.addLayer("regions", { name: "regionBG" });
            if (UserCountryMap.mode != "region") {
                //map.addLayer("regions", { name: "regionBG-2" });
            }
            map.addLayer('regions', {
                key: 'fips',
                name: UserCountryMap.mode != "region" ? "regions2" : "regions",
                click: function(d, p, evt) {
                    evt.stopPropagation();
                }
            });

            map.addSymbols({
                data: map.getLayer('context-clickable').getPathsData(),
                type: $K.Label,
                filter: function(data) { return data.iso != iso; },
                location: function(data) { return 'context-clickable.'+data.iso; },
                text: function(data) { return data.iso; },
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
            parseMetrics(country);
            countryData.push(country);
            countriesByIso[country.iso] = country;
            worldTotalVisits += country['nb_visits_raw'];
        });
        // sort countries by name
        countryData.sort(function(a,b) { return a.name > b.name ? 1 : -1; });

        // store country data globally
        UserCountryMap.countryData = countryData;
        UserCountryMap.countriesByIso = countriesByIso;

        map.loadCSS(config.mapCssPath, function() {
            // map stylesheets are loaded

            // hide loading indicator
            $('#UserCountryMap .loadingPiwik').hide();

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


    $('#UserCountryMap_overlay').hover(function() {
        $('#UserCountryMap_overlay .content').animate({ opacity: 0.01 }, 200);
    }, function() {
        $('#UserCountryMap_overlay .content').animate({ opacity: 1 }, 200);
    });

};


UserCountryMap.ISO2toISO3 = {"BD": "BGD", "BE": "BEL", "BF": "BFA", "BG": "BGR", "BA": "BIH", "BB": "BRB", "WF": "WLF", "BL": "BLM", "BM": "BMU", "BN": "BRN", "BO": "BOL", "BH": "BHR", "BI": "BDI", "BJ": "BEN", "BT": "BTN", "JM": "JAM", "BV": "BVT", "BW": "BWA", "WS": "WSM", "BQ": "BES", "BR": "BRA", "BS": "BHS", "JE": "JEY", "BY": "BLR", "BZ": "BLZ", "RU": "RUS", "RW": "RWA", "RS": "SRB", "TL": "TLS", "RE": "REU", "TM": "TKM", "TJ": "TJK", "RO": "ROU", "TK": "TKL", "GW": "GNB", "GU": "GUM", "GT": "GTM", "GS": "SGS", "GR": "GRC", "GQ": "GNQ", "GP": "GLP", "JP": "JPN", "GY": "GUY", "GG": "GGY", "GF": "GUF", "GE": "GEO", "GD": "GRD", "GB": "GBR", "GA": "GAB", "SV": "SLV", "GN": "GIN", "GM": "GMB", "GL": "GRL", "GI": "GIB", "GH": "GHA", "OM": "OMN", "TN": "TUN", "JO": "JOR", "HR": "HRV", "HT": "HTI", "HU": "HUN", "HK": "HKG", "HN": "HND", "HM": "HMD", "VE": "VEN", "PR": "PRI", "PS": "PSE", "PW": "PLW", "PT": "PRT", "SJ": "SJM", "PY": "PRY", "IQ": "IRQ", "PA": "PAN", "PF": "PYF", "PG": "PNG", "PE": "PER", "PK": "PAK", "PH": "PHL", "PN": "PCN", "PL": "POL", "PM": "SPM", "ZM": "ZMB", "EH": "ESH", "EE": "EST", "EG": "EGY", "ZA": "ZAF", "EC": "ECU", "IT": "ITA", "VN": "VNM", "SB": "SLB", "ET": "ETH", "SO": "SOM", "ZW": "ZWE", "SA": "SAU", "ES": "ESP", "ER": "ERI", "ME": "MNE", "MD": "MDA", "MG": "MDG", "MF": "MAF", "MA": "MAR", "MC": "MCO", "UZ": "UZB", "MM": "MMR", "ML": "MLI", "MO": "MAC", "MN": "MNG", "MH": "MHL", "MK": "MKD", "MU": "MUS", "MT": "MLT", "MW": "MWI", "MV": "MDV", "MQ": "MTQ", "MP": "MNP", "MS": "MSR", "MR": "MRT", "IM": "IMN", "UG": "UGA", "TZ": "TZA", "MY": "MYS", "MX": "MEX", "IL": "ISR", "FR": "FRA", "IO": "IOT", "SH": "SHN", "FI": "FIN", "FJ": "FJI", "FK": "FLK", "FM": "FSM", "FO": "FRO", "NI": "NIC", "NL": "NLD", "NO": "NOR", "NA": "NAM", "VU": "VUT", "NC": "NCL", "NE": "NER", "NF": "NFK", "NG": "NGA", "NZ": "NZL", "NP": "NPL", "NR": "NRU", "NU": "NIU", "CK": "COK", "XK": "XKX", "CI": "CIV", "CH": "CHE", "CO": "COL", "CN": "CHN", "CM": "CMR", "CL": "CHL", "CC": "CCK", "CA": "CAN", "CG": "COG", "CF": "CAF", "CD": "COD", "CZ": "CZE", "CY": "CYP", "CX": "CXR", "CS": "SCG", "CR": "CRI", "CW": "CUW", "CV": "CPV", "CU": "CUB", "SZ": "SWZ", "SY": "SYR", "SX": "SXM", "KG": "KGZ", "KE": "KEN", "SS": "SSD", "SR": "SUR", "KI": "KIR", "KH": "KHM", "KN": "KNA", "KM": "COM", "ST": "STP", "SK": "SVK", "KR": "KOR", "SI": "SVN", "KP": "PRK", "KW": "KWT", "SN": "SEN", "SM": "SMR", "SL": "SLE", "SC": "SYC", "KZ": "KAZ", "KY": "CYM", "SG": "SGP", "SE": "SWE", "SD": "SDN", "DO": "DOM", "DM": "DMA", "DJ": "DJI", "DK": "DNK", "VG": "VGB", "DE": "DEU", "YE": "YEM", "DZ": "DZA", "US": "USA", "UY": "URY", "YT": "MYT", "UM": "UMI", "LB": "LBN", "LC": "LCA", "LA": "LAO", "TV": "TUV", "TW": "TWN", "TT": "TTO", "TR": "TUR", "LK": "LKA", "LI": "LIE", "LV": "LVA", "TO": "TON", "LT": "LTU", "LU": "LUX", "LR": "LBR", "LS": "LSO", "TH": "THA", "TF": "ATF", "TG": "TGO", "TD": "TCD", "TC": "TCA", "LY": "LBY", "VA": "VAT", "VC": "VCT", "AE": "ARE", "AD": "AND", "AG": "ATG", "AF": "AFG", "AI": "AIA", "VI": "VIR", "IS": "ISL", "IR": "IRN", "AM": "ARM", "AL": "ALB", "AO": "AGO", "AN": "ANT", "AQ": "ATA", "AS": "ASM", "AR": "ARG", "AU": "AUS", "AT": "AUT", "AW": "ABW", "IN": "IND", "AX": "ALA", "AZ": "AZE", "IE": "IRL", "ID": "IDN", "UA": "UKR", "QA": "QAT", "MZ": "MOZ"};
UserCountryMap.ISO3toCONT = {"AGO": "AF", "DZA": "AF", "EGY": "AF", "BGD": "AS", "NER": "AF", "LIE": "EU", "NAM": "AF", "BGR": "EU", "BOL": "SA", "GHA": "AF", "CCK": "AS", "PAK": "AS", "CPV": "AF", "JOR": "AS", "LBR": "AF", "LBY": "AF", "MYS": "AS", "DOM": "NA", "PRI": "NA", "SXM": "NA", "PRK": "AS", "PSE": "AS", "TZA": "AF", "BWA": "AF", "KHM": "AS", "UMI": "OC", "NIC": "NA", "TTO": "NA", "ETH": "AF", "PRY": "SA", "HKG": "AS", "SAU": "AS", "LBN": "AS", "SVN": "EU", "BFA": "AF", "CHE": "EU", "MRT": "AF", "HRV": "EU", "CHL": "SA", "CHN": "AS", "KNA": "NA", "SLE": "AF", "JAM": "NA", "SMR": "EU", "GIB": "EU", "DJI": "AF", "GIN": "AF", "FIN": "EU", "URY": "SA", "THA": "AS", "STP": "AF", "SYC": "AF", "NPL": "AS", "CXR": "AS", "LAO": "AS", "YEM": "AS", "BVT": "AN", "ZAF": "AF", "KIR": "OC", "PHL": "AS", "ROU": "EU", "VIR": "NA", "SYR": "AS", "MAC": "AS", "MAF": "NA", "MLT": "EU", "KAZ": "AS", "TCA": "NA", "PYF": "OC", "NIU": "OC", "DMA": "NA", "BEN": "AF", "GUF": "SA", "BEL": "EU", "MSR": "NA", "TGO": "AF", "DEU": "EU", "GUM": "OC", "LKA": "AS", "SSD": "AF", "FLK": "SA", "GBR": "EU", "BES": "NA", "GUY": "SA", "CRI": "NA", "CMR": "AF", "MAR": "AF", "MNP": "OC", "LSO": "AF", "HUN": "EU", "TKM": "AS", "SUR": "SA", "NLD": "EU", "BMU": "NA", "HMD": "AN", "TCD": "AF", "GEO": "AS", "MNE": "EU", "MNG": "AS", "MHL": "OC", "MTQ": "NA", "BLZ": "NA", "NFK": "OC", "MMR": "AS", "AFG": "AS", "BDI": "AF", "VGB": "NA", "BLR": "EU", "BLM": "NA", "GRD": "NA", "TKL": "OC", "GRC": "EU", "RUS": "EU", "GRL": "NA", "SHN": "AF", "AND": "EU", "MOZ": "AF", "TJK": "AS", "XKX": "EU", "HTI": "NA", "MEX": "NA", "ANT": "NA", "ZWE": "AF", "LCA": "NA", "IND": "AS", "LVA": "EU", "BTN": "AS", "VCT": "NA", "VNM": "AS", "NOR": "EU", "CZE": "EU", "ATF": "AN", "ATG": "NA", "FJI": "OC", "IOT": "AS", "HND": "NA", "MUS": "AF", "ATA": "AN", "LUX": "EU", "ISR": "AS", "FSM": "OC", "PER": "SA", "REU": "AF", "IDN": "AS", "VUT": "OC", "MKD": "EU", "COD": "AF", "COG": "AF", "ISL": "EU", "GLP": "NA", "COK": "OC", "COM": "AF", "COL": "SA", "NGA": "AF", "TLS": "OC", "TWN": "AS", "PRT": "EU", "MDA": "EU", "GGY": "EU", "MDG": "AF", "ECU": "SA", "SEN": "AF", "NZL": "OC", "MDV": "AS", "ASM": "OC", "SPM": "NA", "CUW": "NA", "FRA": "EU", "LTU": "EU", "RWA": "AF", "ZMB": "AF", "GMB": "AF", "WLF": "OC", "JEY": "EU", "FRO": "EU", "GTM": "NA", "DNK": "EU", "IMN": "EU", "AUS": "OC", "AUT": "EU", "SJM": "EU", "VEN": "SA", "PLW": "OC", "KEN": "AF", "MYT": "AF", "WSM": "OC", "TUR": "AS", "ALB": "EU", "OMN": "AS", "TUV": "OC", "ALA": "EU", "BRN": "AS", "TUN": "AF", "PCN": "OC", "BRB": "NA", "BRA": "SA", "CIV": "AF", "SRB": "EU", "GNQ": "AF", "USA": "NA", "QAT": "AS", "SWE": "EU", "AZE": "AS", "GNB": "AF", "SWZ": "AF", "TON": "OC", "CAN": "NA", "UKR": "EU", "KOR": "AS", "AIA": "NA", "CAF": "AF", "SVK": "EU", "CYP": "EU", "BIH": "EU", "SGP": "AS", "SGS": "AN", "SOM": "AF", "UZB": "AS", "ERI": "AF", "POL": "EU", "KWT": "AS", "SCG": "EU", "GAB": "AF", "CYM": "NA", "VAT": "EU", "EST": "EU", "MWI": "AF", "ESP": "EU", "IRQ": "AS", "SLV": "NA", "MLI": "AF", "IRL": "EU", "IRN": "AS", "ABW": "NA", "PNG": "OC", "PAN": "NA", "SDN": "AF", "SLB": "OC", "ESH": "AF", "MCO": "EU", "ITA": "EU", "JPN": "AS", "KGZ": "AS", "UGA": "AF", "NCL": "OC", "ARE": "AS", "ARG": "SA", "BHS": "NA", "BHR": "AS", "ARM": "AS", "NRU": "OC", "CUB": "NA"};

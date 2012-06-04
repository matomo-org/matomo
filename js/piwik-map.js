window.UserCountryMap = {};

UserCountryMap.run = function(config) {

    var map = $K.map('#UserCountryMap_map'),
        main = $('#UserCountryMap_container'),
        width = main.width();

    window.__userCountryMap = map;

    function updateMap(svgUrl, callback) {
        map.loadMap(config.svgBasePath + svgUrl, function() {
            var ratio, w, h;

            map.clear();

            ratio = map.viewAB.width / map.viewAB.height;
            w = map.container.width();
            h = w / ratio;
            map.container.height(h-2);
            map.resize(w, h);

            callback();

        }, { padding: -3});
    }

    function renderCountryMap(iso) {

        updateMap(iso + '.svg', function() {
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
                filter: function(data) { return data.iso != iso; },
                location: function(data) { return 'context.'+data.iso; },
                text: function(data) { return data.iso; },
                'class': 'countryLabel'
            });

        });
    }

    function renderWorldMap(target) {
        updateMap(target + '.svg', function() {
            map.addLayer({ id: 'countries', key: 'iso' });

            map.choropleth({ colors: '#eee' });
            // load country data

            /*

            map.choropleth({
               layer: 'countries',
               data: mydata,
               colors: function(d) {
                  return '#f94'; // return color based on data value/object
               }
            });
            */
        });
    }

    map.loadStyles(config.mapCssPath, function() {

        $('#UserCountryMap_content .loadingPiwik').hide();

        renderCountryMap('DEU');
        $('#userCountryMap-update').click(function() {
            var t = $('#userCountryMapInsertID').val();
            if (t.length == 3) {
                renderCountryMap(t);
            } else {
                renderWorldMap(t);
            }
        });
    });


    $('#UserCountryMap_overlay').hover(function() {
        $('#UserCountryMap_overlay').hide();
    });

};

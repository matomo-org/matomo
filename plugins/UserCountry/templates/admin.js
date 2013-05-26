/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {
    $('#geoip-download-progress,#geoip-updater-progressbar').progressbar({value: 1});

    // handle switch current location provider
    $('.location-provider').change(function () {
        if (!$(this).is(':checked')) return; // only handle radio buttons that get checked

        var parent = $(this).parent(),
            loading = $('.loadingPiwik', parent),
            ajaxSuccess = $('.ajaxSuccess', parent);

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.setLoadingElement(loading);
        ajaxRequest.addParams({
            module: 'UserCountry',
            action: 'setCurrentLocationProvider',
            id: $(this).val()
        }, 'get');
        ajaxRequest.setCallback(
            function () {
                ajaxSuccess.fadeIn(1000, function () {
                    setTimeout(function () {
                        ajaxSuccess.fadeOut(1000);
                    }, 2000);
                });
            }
        );
        ajaxRequest.send(false);
    });

    // handle 'refresh location' link click
    $('.refresh-loc').click(function (e) {
        e.preventDefault();

        var cell = $(this).parent().parent(),
            loading = $('.loadingPiwik', cell),
            location = $('.location', cell);

        location.css('visibility', 'hidden');

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.setLoadingElement(loading);
        ajaxRequest.addParams({
            module: 'UserCountry',
            action: 'getLocationUsingProvider',
            id: $(this).attr('data-impl-id')
        }, 'get');
        ajaxRequest.setCallback(
            function (response) {
                location.html('<strong><em>' + response + '</em></strong>').css('visibility', 'visible');
            }
        );
        ajaxRequest.setFormat('html');
        ajaxRequest.send(false);

        return false;
    });

    // geoip database wizard
    var downloadNextChunk = function (action, thisId, progressBarId, cont, extraData, callback) {
        var data = {
            module: 'UserCountry',
            action: action,
            'continue': cont ? 1 : 0
        };
        for (var k in extraData) {
            data[k] = extraData[k];
        }

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams(data, 'post');
        ajaxRequest.setCallback(function (response) {
            if (!response || response.error) {
                callback(response);
            }
            else {
                // update progress bar
                var newProgressVal = Math.ceil((response.current_size / response.expected_file_size) * 100);
                newProgressVal = Math.min(newProgressVal, 100);
                $('#' + progressBarId).progressbar('option', 'value', newProgressVal);

                // if incomplete, download next chunk, otherwise, show updater manager
                if (newProgressVal < 100) {
                    downloadNextChunk(action, thisId, progressBarId, true, extraData, callback);
                }
                else {
                    callback(response);
                }
            }
        });
        ajaxRequest.setErrorCallback(function () {
            callback({error: _pk_translate('UserCountry_FatalErrorDuringDownload_js')});
        });
        ajaxRequest.send(false);
    };

    $('#start-download-free-geoip').click(function () {
        $('#geoipdb-screen1').hide("slide", {direction: "left"}, 800, function () {
            $('#geoipdb-screen2-download').fadeIn(1000);

            // start download of free dbs
            downloadNextChunk(
                'downloadFreeGeoIPDB',
                'geoipdb-screen2-download',
                'geoip-download-progress',
                false,
                {},
                function (response) {
                    if (response.error) {
                        // on error, show error & stop downloading
                        $('#geoipdb-screen2-download').fadeOut(1000, function () {
                            $('#manage-geoip-dbs').html(response.error);
                        });
                    }
                    else {
                        $('#geoipdb-screen2-download').fadeOut(1000, function () {
                            $('#manage-geoip-dbs').html(response.next_screen);
                        });
                    }
                }
            );
        });
    });

    $('body').on('click', '#start-automatic-update-geoip', function () {
        $('#geoipdb-screen1').hide("slide", {direction: "left"}, 800, function () {
            $('#geoip-db-mangement').text(_pk_translate('UserCountry_SetupAutomaticUpdatesOfGeoIP_js'));
            $('#geoipdb-update-info').fadeIn(1000);
        });
    });

    $('body').on('click', '#update-geoip-links', function () {
        $('#geoipdb-update-info-error').hide();

        var currentDownloading = null,
            updateGeoIPSuccess = function (response) {
                if (response && response.error) {
                    $('#geoip-progressbar-container').hide();
                    $('#geoipdb-update-info-error').html(response.error).show();
                }
                else if (response && response.to_download) {
                    var continuing = currentDownloading == response.to_download;
                    currentDownloading = response.to_download;

                    // show progress bar w/ message
                    $('#geoip-updater-progressbar').progressbar('option', 'value', 1);
                    $('#geoip-updater-progressbar-label').html(response.to_download_label);
                    $('#geoip-progressbar-container').show();

                    // start/continue download
                    downloadNextChunk(
                        'downloadMissingGeoIpDb', 'geoipdb-update-info', 'geoip-updater-progressbar',
                        continuing, {key: response.to_download}, updateGeoIPSuccess);
                }
                else {
                    $('#geoipdb-update-info-error').hide();
                    $('#geoip-updater-progressbar-label').html('');
                    $('#geoip-progressbar-container').hide();

                    // fade in/out Done message
                    $('#done-updating-updater').fadeIn(1000, function () {
                        setTimeout(function () {
                            $('#done-updating-updater').fadeOut(1000);
                        }, 3000);
                    });
                }
            };

        // setup the auto-updater
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            period: $('#geoip-update-period-cell').find('>input:checked').val()
        }, 'get');
        ajaxRequest.addParams({
            module: 'UserCountry',
            action: 'updateGeoIPLinks',
            loc_db: $('#geoip-location-db').val(),
            isp_db: $('#geoip-isp-db').val(),
            org_db: $('#geoip-org-db').val()
        }, 'post');
        ajaxRequest.setCallback(updateGeoIPSuccess);
        ajaxRequest.send(false);
    });
});

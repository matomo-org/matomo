/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {
    $('#geoip-download-progress,#geoip-updater-progressbar').progressbar({value: 1});

    // handle switch current location provider
    $('.location-provider').change(function () {
        if (!$(this).is(':checked')) return; // only handle radio buttons that get checked

        var parent = $(this).closest('p'),
            loading = $('.loadingPiwik', parent),
            ajaxSuccess = $('.success', parent);

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.setLoadingElement(loading);
        ajaxRequest.addParams({
            module: 'UserCountry',
            action: 'setCurrentLocationProvider',
            id: $(this).val()
        }, 'get');
        ajaxRequest.setCallback(
            function () {
                var UI = require('piwik/UI');
                var notification = new UI.Notification();
                notification.show(_pk_translate('General_Done'), {
                    placeat: ajaxSuccess,
                    context: 'success',
                    noclear: true,
                    type: 'toast',
                    style: {display:'inline-block', marginTop: '10px'},
                    id: 'userCountryLocationProvider'
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
            callback({error: _pk_translate('UserCountry_FatalErrorDuringDownload')});
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

    $('body')
        .on('click', '#start-automatic-update-geoip', function () {
            $('#geoipdb-screen1').hide("slide", {direction: "left"}, 800, function () {
                $('#geoip-db-mangement').text(_pk_translate('UserCountry_SetupAutomaticUpdatesOfGeoIP'));
                $('#geoipdb-update-info').fadeIn(1000);
            });
        })
        .on('click', '#update-geoip-links', function () {
            var currentDownloading = null;
            var updateGeoIPSuccess = function (response) {
                if (response && response.error) {
                    $('#geoip-progressbar-container').hide();

                    var UI = require('piwik/UI');
                    var notification = new UI.Notification();
                    notification.show(response.error, {
                        placeat: '#geoipdb-update-info-error',
                        context: 'error',
                        style: {display: 'inline-block'},
                        id: 'userCountryGeoIpUpdate'
                    });

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
                    $('#geoip-updater-progressbar-label').html('');
                    $('#geoip-progressbar-container').hide();

                    var UI = require('piwik/UI');
                    var notification = new UI.Notification();
                    notification.show(_pk_translate('General_Done'), {
                        placeat: '#done-updating-updater',
                        context: 'success',
                        noclear: true,
                        type: 'toast',
                        style: {display: 'inline-block'},
                        id: 'userCountryGeoIpUpdate'
                    });

                    $('#geoip-updater-next-run-time').html(response.nextRunTime).parent().effect('highlight', {color: '#FFFFCB'}, 2000);
                }
            };

            // setup the auto-updater
            var ajaxRequest = new ajaxHelper();
            var periodSelected = $('#geoip-update-period-cell').find('input:checked').val();
            ajaxRequest.addParams({
                period: periodSelected
            }, 'get');
            ajaxRequest.addParams({
                module: 'UserCountry',
                action: 'updateGeoIPLinks',
                token_auth: piwik.token_auth,
                loc_db: $('#geoip-location-db').val(),
                isp_db: $('#geoip-isp-db').val(),
                org_db: $('#geoip-org-db').val()
            }, 'post');
            ajaxRequest.setCallback(updateGeoIPSuccess);
            ajaxRequest.send(false);
        }
    );
});

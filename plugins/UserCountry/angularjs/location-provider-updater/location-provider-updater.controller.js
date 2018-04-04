/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('LocationProviderUpdaterController', LocationProviderUpdaterController);

    LocationProviderUpdaterController.$inject = ['piwikApi'];

    function LocationProviderUpdaterController(piwikApi) {
        // remember to keep controller very simple. Create a service/factory (model) if needed
        var self = this;

        this.buttonUpdateSaveText = _pk_translate('General_Save');
        this.progressUpdateLabel = '';

        // geoip database wizard
        var downloadNextChunk = function (action, thisId, progressBarId, cont, extraData, callback) {
            var data = {};
            for (var k in extraData) {
                data[k] = extraData[k];
            }

            piwikApi.withTokenInUrl();
            piwikApi.post({
                module: 'UserCountry',
                action: action,
                'continue': cont ? 1 : 0
            }, data).then(function (response) {
                if (!response || response.error) {
                    callback(response);
                } else {
                    // update progress bar
                    var newProgressVal = Math.ceil((response.current_size / response.expected_file_size) * 100);
                    self[progressBarId] = Math.min(newProgressVal, 100);

                    // if incomplete, download next chunk, otherwise, show updater manager
                    if (newProgressVal < 100) {
                        downloadNextChunk(action, thisId, progressBarId, true, extraData, callback);
                    } else {
                        callback(response);
                    }
                }
            }, function () {
                callback({error: _pk_translate('UserCountry_FatalErrorDuringDownload')});
            });
        };

        this.startDownloadFreeGeoIp = function () {
            this.showFreeDownload = true;
            this.showPiwikNotManagingInfo = false;

            this.progressFreeDownload = 0;

            // start download of free dbs
            downloadNextChunk(
                'downloadFreeGeoIPDB',
                'geoipdb-screen2-download',
                'progressFreeDownload',
                false,
                {},
                function (response) {
                    if (response.error) {
                        $('#geoipdb-update-info').html(response.error);
                        self.geoipDatabaseInstalled = true;
                    } else {
                        self.showGeoIpUpdateInfo();
                    }
                }
            );
        };

        this.startAutomaticUpdateGeoIp = function () {
            this.buttonUpdateSaveText = _pk_translate('General_Continue');
            this.showGeoIpUpdateInfo();
        };

        this.showGeoIpUpdateInfo = function () {
            this.geoipDatabaseInstalled = true;

            // todo we need to replace this the proper way eventually
            $('#geoip-db-mangement .card-title').text(_pk_translate('UserCountry_SetupAutomaticUpdatesOfGeoIP'));
        }

        this.saveGeoIpLinks = function () {
            var currentDownloading = null;
            var updateGeoIPSuccess = function (response) {
                if (response && response.error) {
                    self.isUpdatingGeoIpDatabase = false;

                    var UI = require('piwik/UI');
                    var notification = new UI.Notification();
                    notification.show(response.error, {
                        placeat: '#geoipdb-update-info-error',
                        context: 'error',
                        style: {display: 'inline-block'},
                        id: 'userCountryGeoIpUpdate'
                    });

                } else if (response && response.to_download) {
                    var continuing = currentDownloading == response.to_download;
                    currentDownloading = response.to_download;

                    // show progress bar w/ message
                    self.progressUpdateDownload = 0;
                    self.progressUpdateLabel = response.to_download_label;
                    self.isUpdatingGeoIpDatabase = true;

                    // start/continue download
                    downloadNextChunk(
                        'downloadMissingGeoIpDb', 'geoipdb-update-info', 'progressUpdateDownload',
                        continuing, {key: response.to_download}, updateGeoIPSuccess);

                } else {
                    self.progressUpdateLabel = '';
                    self.isUpdatingGeoIpDatabase = false;

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

            piwikApi.withTokenInUrl();
            piwikApi.post({
                period: this.updatePeriod,
                module: 'UserCountry',
                action: 'updateGeoIPLinks'
            }, {
                loc_db: this.locationDbUrl,
                isp_db: this.ispDbUrl
            }).then(updateGeoIPSuccess);
        };


    }
})();
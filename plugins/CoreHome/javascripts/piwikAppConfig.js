var piwikAppConfig = angular.module('piwikApp.config', []);

(function () {
    for (var index in piwik.config) {
        piwikAppConfig.constant(index.toUpperCase(), piwik.config[index])
    };
})()
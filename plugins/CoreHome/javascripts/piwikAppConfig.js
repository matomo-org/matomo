var piwikAppConfig = angular.module('piwikApp.config', []);

(function () {
    // we probably want this later as a separate config file, till then it serves as a "bridge"
    for (var index in piwik.config) {
        piwikAppConfig.constant(index.toUpperCase(), piwik.config[index])
    };
})()
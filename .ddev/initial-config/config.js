exports.piwikUrl = "https://matomo.ddev.site/";
exports.phpServer = {
    HTTP_HOST: 'matomo.ddev.site',
    REQUEST_URI: '/',
    REMOTE_ADDR: '127.0.0.1'
};

// browserConfig (including the Chrome/Chromium executable path) is provided by tests/UI/config.dist.js,
// which resolves the Chromium the ddev web image installs. Local screenshots may still differ
// slightly from the CI-generated expected screenshots.

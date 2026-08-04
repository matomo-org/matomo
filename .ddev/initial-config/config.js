exports.piwikUrl = "https://matomo.ddev.site/";
exports.phpServer = {
    HTTP_HOST: 'matomo.ddev.site',
    REQUEST_URI: '/',
    REMOTE_ADDR: '127.0.0.1'
};

// browserConfig (including the Chrome executable path) is provided by tests/UI/config.dist.js,
// which resolves the Chrome for Testing version pinned in
// tests/lib/screenshot-testing/.puppeteerrc.cjs -- the same browser CI uses, so local screenshots
// should match the expected ones. If it warns about falling back to the ddev web image's Chromium
// instead, the pinned browser has not been downloaded yet: run
// `ddev exec 'cd tests/lib/screenshot-testing && npm run install-browser'`.

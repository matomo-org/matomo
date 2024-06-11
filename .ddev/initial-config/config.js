exports.piwikUrl = "https://matomo.ddev.site/";
exports.phpServer = {
    HTTP_HOST: 'matomo.ddev.site',
    REQUEST_URI: '/',
    REMOTE_ADDR: '127.0.0.1'
};
exports.browserConfig = {
  args: ['--no-sandbox', '--ignore-certificate-errors'],
  executablePath: '/usr/bin/chromium'
};


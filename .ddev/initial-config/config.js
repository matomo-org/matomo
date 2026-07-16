const fs = require('fs');

exports.piwikUrl = "https://matomo.ddev.site/";
exports.phpServer = {
    HTTP_HOST: 'matomo.ddev.site',
    REQUEST_URI: '/',
    REMOTE_ADDR: '127.0.0.1'
};

const browserConfig = {
  args: ['--no-sandbox', '--ignore-certificate-errors']
};

// Puppeteer's bundled Chromium is amd64-only. When running on an arm64
// container (e.g. Apple Silicon without Rosetta), fall back to the system
// Chromium so UI tests still execute -- screenshots won't match CI in that
// case (the configure-platform.sh hook prints a warning).
if (process.arch !== 'x64' && fs.existsSync('/usr/bin/chromium')) {
  browserConfig.executablePath = '/usr/bin/chromium';
}

exports.browserConfig = browserConfig;

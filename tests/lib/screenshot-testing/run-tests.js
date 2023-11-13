/*!
 * Matomo - free/libre analytics platform
 *
 * UI test runner script
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

process.env.NODE_TLS_REJECT_UNAUTHORIZED = 0; // ignore ssl errors

const path = require('path');
const puppeteer = require('puppeteer');
const setUpGlobals = require('./support/globals.js');
const Mocha = require('mocha');
const chai = require('chai');
const chaiFiles = require('chai-files');
require('./support/fs-extras');

main();

async function main() {
    const browser = await puppeteer.launch({ args: ['--no-sandbox', '--ignore-certificate-errors'] });
    const webpage = await browser.newPage();
    await webpage._client.send('Animation.setPlaybackRate', { playbackRate: 50 }); // make animations run 50 times faster, so we don't have to wait as much

    // required modules
    let config = require("./../../UI/config.dist");
    try {
        config = Object.assign({}, config, require("./../../UI/config"));
    } catch (e) {
        // ignore
    }

    // assume the URI points to a folder and make sure Piwik won't cut off the last path segment
    if (config.phpServer.REQUEST_URI.slice(-1) !== '/') {
        config.phpServer.REQUEST_URI += '/';
    }

    const originalUserAgent = await browser.userAgent();

    setUpGlobals(config, webpage, originalUserAgent);

    mocha = new Mocha({
        ui: 'bdd',
        bail: false,
        reporter: config.reporter,
        reporterOptions: config.reporterOptions,
        color: 1,
        timeout: options.timeout || 240000,
    });

    const imageAssert = require('./support/chai-extras');
    chai.use(imageAssert());
    chai.use(chaiFiles);

    // run script
    if (options['help']) {
        app.printHelpAndExit();
    }

    // the mocha-super-suite imports the individual specs. kept for expedience when converting to chromium
    // headless.
    mocha.addFile(path.join(__dirname, 'mocha-super-suite.js'));

    app.runTests(mocha)
}

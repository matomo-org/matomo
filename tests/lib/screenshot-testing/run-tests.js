/*!
 * Piwik - free/libre analytics platform
 *
 * UI test runner script
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

process.env.NODE_TLS_REJECT_UNAUTHORIZED = 0; // ignore ssl errors

const path = require('path');
const puppeteer = require('puppeteer');
const setUpGlobals = require('./support/globals.js');
const Mocha = require('mocha');
const chai = require('chai');
const resemble = require('resemblejs');
require('./support/fs-extras');

main();

async function main() {
    const browser = await puppeteer.launch({ args: ['--no-sandbox'] });
    const page = await browser.newPage();

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

    setUpGlobals(config, page);

    mocha = new Mocha({
        ui: 'bdd',
        reporter: config.reporter,
        bail: false,
        useColors: true,
        timeout: 240000, // TODO: make configurable via CLI
    });

    const imageAssert = require('./support/chai-extras');
    chai.use(imageAssert());

    // TODO: outputSettings is deprecated, should use in chai
    resemble.outputSettings({
        errorColor: {
            red: 255,
            green: 0,
            blue: 0,
            alpha: 125
        },
        errorType: 'movement',
        transparency: 0.3,
        largeImageThreshold: 20000,
    });

    // TODO: implement load timeout? not sure if it's needed.
    /* TODO: timeout should be global mocha timeout
    timeout = setTimeout(function () {
        var timeoutDetails = "";
        timeoutDetails += "Page is loading: " + self._isLoading + "\n";
        timeoutDetails += "Initializing: " + self._isInitializing + "\n";
        timeoutDetails += "Navigation requested: " + self._isNavigationRequested + "\n";
        timeoutDetails += "Pending AJAX request count: " + self._getAjaxRequestCount() + "\n";
        timeoutDetails += "Loading images count: " + self._getImageLoadingCount() + "\n";
        timeoutDetails += "Remaining resources: " + JSON.stringify(self._resourcesRequested) + "\n";

        self.abort();

        callback(new Error("Screenshot load timeout. Details:\n" + timeoutDetails));
    }, 240 * 1000);
    */

    // run script
    if (options['help']) {
        app.printHelpAndExit();
    }

    // the mocha-super-suite imports the individual specs. kept for expedience when converting to chromium
    // headless.
    mocha.addFile(path.join(__dirname, 'mocha-super-suite.js'));

    app.runTests(mocha)
}

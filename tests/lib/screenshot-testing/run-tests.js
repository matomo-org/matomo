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
        useColors: true
    });

    require('./support/chai-extras');

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
        largeImageThreshold: 20000
    });

    // run script
    if (options['help']) {
        app.printHelpAndExit();
    }

    // TODO: note about why doing this
    mocha.addFile(path.join(__dirname, 'mocha-super-suite.js'));

    app.runTests(mocha)
}

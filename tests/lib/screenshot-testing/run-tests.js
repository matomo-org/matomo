/*!
 * Piwik - free/libre analytics platform
 *
 * UI test runner script
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// required modules
config = require("./../../UI/config.dist");
try {
    var localConfig = require("./../../UI/config");
} catch (e) {
    localConfig = null;
}

if (localConfig) {
    for (var prop in localConfig) {
        if (localConfig.hasOwnProperty(prop)) {
            config[prop] = localConfig[prop];
        }
    }
}

// assume the URI points to a folder and make sure Piwik won't cut off the last path segment
if (config.phpServer.REQUEST_URI.slice(-1) != '/') {
    config.phpServer.REQUEST_URI += '/';
}

require('./support/fs-extras');

phantom.injectJs('./support/globals.js');

// make sure script works wherever it's executed from
require('fs').changeWorkingDirectory(__dirname);

// load mocha + chai
require('./support/mocha-loader');
phantom.injectJs(chaiPath);
require('./support/chai-extras');

// load & configure resemble (for comparison)
phantom.injectJs(resemblePath);

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

app.init();
app.loadTestModules();
app.runTests();

/*!
 * Piwik - Web Analytics
 *
 * UI test runner script
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// required modules
var fs = require('fs'),
    path = require('./support/path'),
    config = require("./config");

phantom.injectJs('./support/globals.js');

// make sure script works wherever it's executed from
fs.changeWorkingDirectory(__dirname);

// load mocha + chai
require('./support/mocha-loader');
phantom.injectJs(chaiPath);
require('./support/chai-extras');

// run script
if (options['help']) {
    app.printHelpAndExit();
}

app.loadTestModules();
app.runTests();
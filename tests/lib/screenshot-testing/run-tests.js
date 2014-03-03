/*!
 * Piwik - Web Analytics
 *
 * UI test runner script
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// required modules
var config = require("./config");

require('./support/fs-extras');

phantom.injectJs('./support/globals.js');

// make sure script works wherever it's executed from
require('fs').changeWorkingDirectory(__dirname);

// load mocha + chai
require('./support/mocha-loader');
phantom.injectJs(chaiPath);
require('./support/chai-extras');

// run script
if (options['help']) {
    app.printHelpAndExit();
}

options['tests'] = ['ActionsDataTable', 'BarGraph', 'Dashboard']; // TODO: remove after debugging testing done

app.init();
app.loadTestModules();
app.runTests();
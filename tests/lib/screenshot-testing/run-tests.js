/*!
 * Piwik - free/libre analytics platform
 *
 * UI test runner script
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var thisDir = require('./src/platform').getLibraryRootDir();

var Jambalaya = require('./node_modules/jambalaya'),
    iocConfig = require('./config/config.json'),
    container = new Jambalaya(iocConfig, thisDir + '/src'),

    config = container.get('config'),
    platform = container.get('platform');

phantom.injectJs('./src/globals.js');

platform.init();
platform.changeWorkingDirectory(thisDir);

var app = app = container.get('app');
app.run();
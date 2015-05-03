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

    platform = container.get('platform');

platform.init();
platform.changeWorkingDirectory(thisDir);

container.get('app').run();
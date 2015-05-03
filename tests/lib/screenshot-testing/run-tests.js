/*!
 * Piwik - free/libre analytics platform
 *
 * UI test runner script
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var platformId = 'phantomjs';

var iocConfig = require('./config/config.json'),
    platformConfig = require('./config/' + platformId + '.json');

for (var key in platformConfig) {
    if (platformConfig.hasOwnProperty(key)) {
        iocConfig[key] = platformConfig[key];
    }
}

var thisDir = require('./src/platform/' + platformId).getLibraryRootDir();
var Jambalaya = require('./node_modules/jambalaya'),
    container = new Jambalaya(iocConfig, thisDir + '/src'),

    config = container.get('config'), // setting this var here makes it a global
    platform = container.get('platform');

platform.init();
platform.changeWorkingDirectory(thisDir);

var app = container.get('app'); // needs to be a global
app.run();
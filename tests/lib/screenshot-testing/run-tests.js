/*!
 * Piwik - free/libre analytics platform
 *
 * UI test runner script
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var _container = (function () {
    function detectPlatform() {
        if (typeof phantom !== 'undefined') {
            return 'phantomjs';
        } else if (isElectron()) {
            return 'electron';
        } else {
            throw new Error("Don't know what platform the tests are running under, currently recognized platforms are: phantomjs, electron");
        }
    }

    function isElectron() {
        try {
            require('browser-window');
            return true;
        } catch (e) {
            return false;
        }
    }

    var platformId = detectPlatform();

    var iocConfig = require('./config/config.json'),
        platformConfig = require('./config/' + platformId + '.json');

    for (var key in platformConfig) {
        if (platformConfig.hasOwnProperty(key)) {
            iocConfig[key] = platformConfig[key];
        }
    }

    var thisDir = require('./src/platform/' + platformId).getLibraryRootDir();
    var Jambalaya = require('./node_modules/jambalaya'),
        container = new Jambalaya(iocConfig, thisDir + '/src');

    container.get('platform').changeWorkingDirectory(thisDir);

    return container;
}());

var config = _container.get('config'); // setting these vars here makes them globals

_container.get('platform').init();
_container.get('chai-loader').initExtras();
_container.get('mocha-loader').load();
_container.get('resemble-loader').load();
_container.get('platform').runApp(_container.get('app'));
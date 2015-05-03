/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var path = require('path'),
    fs = require('fs');

function Platform(config, app) {
    this.config = config;
    this.app = app;
}

Platform.prototype.init = function () {
    require('../fs-extras');

    // TODO
};

Platform.prototype.runApp = function (app) {
    var electronApp = require('app');
    electronApp.on('ready', function () {
        app.run();
    });
};

Platform.prototype.changeWorkingDirectory = function (path) {
    process.chdir(path);
};

exports.Platform = Platform;

exports.getLibraryRootDir = function () {
    return path.join(__dirname, '..', '..');
};
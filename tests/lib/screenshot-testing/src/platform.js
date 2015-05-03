/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function Platform() {
    // empty
}

Platform.prototype.init = function () {
    require('./fs-extras');

    phantom.injectJs('./globals.js');

    // load mocha + chai
    require('./mocha-loader');
    phantom.injectJs(chaiPath);
    require('./chai-extras');

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
        transparency: 0.3
    })
;};

Platform.prototype.changeWorkingDirectory = function (toDirectory) {
    require('fs').changeWorkingDirectory(toDirectory);
};

exports.Platform = Platform;

exports.getLibraryRootDir = function () {
    return phantom.libraryPath;
};
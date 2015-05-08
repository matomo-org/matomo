/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function ResembleLoader() {
    // empty
}

ResembleLoader.prototype.load = function () {
    resemble.outputSettings({
        errorColor: {
            red: 255,
            green: 0,
            blue: 0,
            alpha: 125
        },
        errorType: 'movement',
        transparency: 0.3
    });
};

exports.ResembleLoader = ResembleLoader;
/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function MochaLoader(config) {
    this.config = config;
}

MochaLoader.prototype.load = function () {
    mocha.setup({
        ui: 'bdd',
        reporter: this.config.reporter,
        bail: false
    });
};

exports.MochaLoader = MochaLoader;
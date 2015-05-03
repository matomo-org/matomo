/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function Config() {
    var config = require("../../../UI/config.dist");
    this.setProperties(config);

    var localConfig = null;
    try {
        localConfig = require("../../../UI/config");
    } catch (e) {
        // empty
    }

    this.setProperties(localConfig || {});

    // assume the URI points to a folder and make sure Piwik won't cut off the last path segment
    if (this.phpServer.REQUEST_URI.slice(-1) != '/') {
        this.phpServer.REQUEST_URI += '/';
    }
}

Config.prototype.setProperties = function (values) {
    for (var prop in values) {
        if (values.hasOwnProperty(prop)) {
            this[prop] = values[prop];
        }
    }
};

exports.Config = Config;
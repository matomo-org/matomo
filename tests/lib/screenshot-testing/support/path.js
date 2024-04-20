/*!
 * Matomo - free/libre analytics platform
 *
 * path related functions
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

exports.join = function () {
    return Array.prototype.join.call(arguments, "/").replace(/[\\\/]{2,}/g, "/");
};

exports.dirname = function (path) {
    var lastSeparator = path.lastIndexOf("/");
    return lastSeparator == -1 ? path : path.substring(0, lastSeparator);
};

exports.basename = function (path) {
    var lastSeparator = path.lastIndexOf("/");
    return lastSeparator == -1 ? path : path.substring(lastSeparator + 1);
};

exports.resolve = function (path) {
    if (path.charAt(0) != '/') {
        path = exports.join(__dirname, path);
    }

    var path_split = path.split('/'),
        result = [];

    for (var i = 0; i != path_split.length; ++i) {
        if (path_split[i] == '.') {
            continue;
        } else if (path_split[i] == '..') {
            result.pop();
        } else {
            result.push(path_split[i]);
        }
    }

    return exports.join.apply(exports, result);
};
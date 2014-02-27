/*!
 * Piwik - Web Analytics
 *
 * path related functions
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

exports.join = function () {
    return Array.prototype.join.call(arguments, "/").replace(/[\\\/]{2,}/g, "/");
};

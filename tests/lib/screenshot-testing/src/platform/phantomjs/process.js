/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// phantomjs process shunt (mimic's some functionality of node.js' process object so screenshot testing will work
// on both environments). must be injected, not require'd.
var process = {
    exit: function (code) {
        phantom.exit(code);
    }
};
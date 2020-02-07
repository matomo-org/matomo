/*!
 * Piwik - free/libre analytics platform
 *
 * PageRenderer class for screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

exports.parse = function () {
    var result = {tests: []};

    var args = process.argv;
    for (var i = 1; i < args.length; ++i) {
        var arg = args[i];
        if (arg[0] === '-') {
            var matches = arg.match(/-*([^=]+)(?:=(.*))?/),
                key = matches[1],
                value = matches[2];

            result[key.toString()] = value || true;
        } else if (arg) {
            result.tests.push(arg);
        }
    }

    result.tests.shift();

    return result;
};
/*!
 * Piwik - free/libre analytics platform
 *
 * phantomjs overrides & extras required to allow mocha to run w/ console output + mocha
 * loading logic
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var fs = require("fs");

// setup mocha (add stdout.write function & configure style + reporter)
mocha.constructor.process.stdout = {
    write: function (data) {
        fs.write("/dev/stdout", data, "w");
    }
};

mocha.setup({
    ui: 'bdd',
    reporter: config.reporter,
    bail: false
});

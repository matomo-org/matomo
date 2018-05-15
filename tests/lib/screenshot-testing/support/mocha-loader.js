/*!
 * Piwik - free/libre analytics platform
 *
 * phantomjs overrides & extras required to allow mocha to run w/ console output + mocha
 * loading logic
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var path = require('./path'),
    fs = require("fs"),
    sprintf = require('./sprintf').sprintf;

// phantomjs does not have Function.prototype.bind
Function.prototype.bind = function () {
    var f = this,
        boundArguments = [],
        thisArg = arguments[0];

    for (var i = 1; i < arguments.length; ++i) {
        boundArguments.push(arguments[i]);
    }

    return function () {
        var args = [].concat(boundArguments);
        Array.prototype.push.apply(args, arguments);

        return f.apply(thisArg, args);
    };
};

// phantomjs console.log/console.error must support sprintf params for mocha
var sprintfWrappedFunc = function (original) {
    return function () {
        var arrayArgs = [];
        for (var i = 0; i < arguments.length; ++i) {
            arrayArgs.push(arguments[i]);
        }

        if (arrayArgs.length > 0) {
            if (typeof arrayArgs[0] === 'undefined') {
                arrayArgs[0] = 'undefined';
            } else {
                arrayArgs[0] = arrayArgs[0].toString();
            }
        }

        var message = arrayArgs[0];
        try {
            message = sprintf.apply(null, arrayArgs);
        } catch (e) {
            // ignore
        }

        original.call(console, message);
    };
};

console.log = sprintfWrappedFunc(console.log);
console.error = sprintfWrappedFunc(console.error);

// load mocha
phantom.injectJs(mochaPath);

// setup mocha (add stdout.write function & configure style + reporter)
mocha.constructor.process.stdout = {
    write: function (data) {
        fs.write("/dev/stdout", data, "w");
    }
};

mocha.setup({
    ui: 'bdd',
    reporter: config.reporter,
    bail: false,
    useColors: true
});

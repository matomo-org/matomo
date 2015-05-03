/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var path = require('path'),
    fs = require("fs"),
    sprintf = require('./phantomjs/sprintf').sprintf;

function Platform(config) {
    this.config = config;
}

Platform.prototype.init = function () {
    this.addMissingNodeFunctions();

    require('../fs-extras');

    phantom.injectJs('./src/platform/phantomjs/process.js');

    phantom.injectJs('./src/globals.js');

    var testsLibDir = path.join(phantom.libraryPath, "..", "..", "lib");

    // load mocha
    var mochaPath = path.join(testsLibDir, this.config.mocha, "mocha.js");
    phantom.injectJs(mochaPath);

    // setup mocha (add stdout.write function & configure style + reporter)
    mocha.constructor.process.stdout = {
        write: function (data) {
            fs.write("/dev/stdout", data, "w");
        }
    };

    // load chai
    var chaiPath = path.join(testsLibDir, this.config.chai, "chai.js");
    phantom.injectJs(chaiPath);

    // load & configure resemble (for comparison)
    var resemblePath = path.join(testsLibDir, 'resemblejs', 'resemble.js');
    phantom.injectJs(resemblePath);
};

Platform.prototype.addMissingNodeFunctions = function () {
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
};

Platform.prototype.changeWorkingDirectory = function (toDirectory) {
    require('fs').changeWorkingDirectory(toDirectory);
};

Platform.prototype.runApp = function (app) {
    app.run();
};

exports.Platform = Platform;

exports.getLibraryRootDir = function () {
    return phantom.libraryPath;
};
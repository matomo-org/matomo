/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var path = require('path'),
    fs = require('fs');

function Platform(config, app) {
    this.config = config;
    this.app = app;

    this.electronApp = require('app');

    var self = this;
    this.electronApp.on('window-all-closed', function() {
        if (process.platform != 'darwin') {
            self.electronApp.quit();
        }
    });
}

Platform.prototype.init = function () {
    this.addPlatformSpecificExtras();

    require('../fs-extras');

    var libraryRootDir = exports.getLibraryRootDir();

    // setup simple globals
    global.PIWIK_INCLUDE_PATH = path.join(libraryRootDir, '..', '..', '..');

    global.expect = function () {
        return chai.expect.apply(chai.expect, arguments);
    };

    global.options = require('../parse-cli-args').parse(process.argv);

    global.window = global;
    global.document = global;
    global.location = {search: ''};

    var testsLibDir = path.join(libraryRootDir, "..", "..", "lib");

    // load mocha
    var mochaPath = path.join(testsLibDir, this.config.mocha, "mocha.js");
    require(mochaPath);

    // setup mocha (add stdout.write function)
    mocha.constructor.process.stdout = {
        write: function (data) {
            process.stdout.write(data);
        }
    };

    // load chai
    var chaiPath = path.join(testsLibDir, this.config.chai, "chai.js");

    global.chai = require(chaiPath);

    // load & configure resemble (for comparison)
    var resemblePath = path.join(testsLibDir, 'resemblejs', 'resemble.js'),
        resembleModule = require(resemblePath);

    for (var key in resembleModule) {
        if (resembleModule.hasOwnProperty(key)) {
            global[key] = resembleModule[key];
        }
    }
};

Platform.prototype.addPlatformSpecificExtras = function () {
    fs.isDir = function (path) {
        try {
            return fs.statSync(path).isDirectory();
        } catch (e) {
            return false;
        }
    };

    fs.isFile = function (path) {
        try {
            return fs.statSync(path).isFile();
        } catch (e) {
            return false;
        }
    };

    fs.isLink = function (path) {
        try {
            return fs.statSync(path).isSymbolicLink();
        } catch (e) {
            return false;
        }
    };
};

Platform.prototype.setupGlobals = function (testEnvironment) {
    global.testEnvironment = testEnvironment;
};

Platform.prototype.runApp = function (app) {
    app.run();
};

Platform.prototype.changeWorkingDirectory = function (path) {
    process.chdir(path);
};

exports.Platform = Platform;

exports.getLibraryRootDir = function () {
    return path.join(__dirname, '..', '..');
};
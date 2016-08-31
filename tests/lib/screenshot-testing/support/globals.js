/*!
 * Piwik - free/libre analytics platform
 *
 * UI tests config
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var path = require('./support/path');

var __dirname = phantom.libraryPath;

var PIWIK_INCLUDE_PATH = path.join(__dirname, '..', '..', '..');

var uiTestsDir = path.join(PIWIK_INCLUDE_PATH, 'tests', 'UI');

var testsLibDir = path.join(__dirname, "..", "..", "lib");

var mochaPath = path.join(testsLibDir, config.mocha, "mocha.js");

var chaiPath = path.join(testsLibDir, config.chai, "chai.js");

var resemblePath = path.join(testsLibDir, 'resemblejs', 'resemble.js');

var expect = function () {
    return chai.expect.apply(chai.expect, arguments);
};

var options = require('./support/parse-cli-args').parse();

var testEnvironment = require('./support/test-environment').TestingEnvironment;

var app = require('./support/app').Application;
/*!
 * Piwik - free/libre analytics platform
 *
 * UI tests config
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const path = require('path');
const chai = require('chai');
const { PageRenderer } = require('./page-renderer');

module.exports = function setUpGlobals(config, page) {
    global.config = config;

    global.PIWIK_INCLUDE_PATH = path.join(__dirname, '..', '..', '..', '..');
    global.uiTestsDir = path.join(PIWIK_INCLUDE_PATH, 'tests', 'UI');
    global.testsLibDir = path.join(__dirname, "..", "..", "lib");
    global.mochaPath = path.join(testsLibDir, config.mocha, "mocha.js");
    global.chaiPath = path.join(testsLibDir, config.chai, "chai.js");
    global.resemblePath = path.join(testsLibDir, 'resemblejs', 'resemble.js');
    global.options = require('./parse-cli-args').parse();
    global.testEnvironment = require('./test-environment').TestingEnvironment;
    global.app = require('./app').Application;
    global.expect = chai.expect;
    global.page = new PageRenderer(config.piwikUrl + path.join("tests", "PHPUnit", "proxy"), page);
};

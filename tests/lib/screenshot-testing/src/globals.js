/*!
 * Piwik - free/libre analytics platform
 *
 * UI tests config
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var path = require('path');

var PIWIK_INCLUDE_PATH = path.join(phantom.libraryPath, '..', '..', '..');

var expect = function () {
    return chai.expect.apply(chai.expect, arguments);
};

var options = require('./src/parse-cli-args').parse();

var testEnvironment = require('./src/test-environment').TestingEnvironment;
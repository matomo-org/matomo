/*!
 * Piwik - Web Analytics
 *
 * Test environment overriding
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    testingEnvironmentOverridePath = path.join(PIWIK_INCLUDE_PATH, '/tmp/testingPathOverride.json');

var TestingEnvironment = function () {
    if (fs.exists(testingEnvironmentOverridePath)) {
        var data = JSON.parse(fs.read(testingEnvironmentOverridePath));
        for (var key in data) {
            this[key] = data[key];
        }
    }
};

TestingEnvironment.prototype.save = function () {
    fs.write(testingEnvironmentOverridePath, JSON.stringify(this));
};

TestingEnvironment.prototype.callApi = function (method, params, done) {
    params.module = "API";
    params.method = method;
    params.format = 'json';

    var url = path.join(config.piwikUrl, "tests/PHPUnit/proxy/index.php?");
    for (var key in params) {
        url += key + "=" + encodeURIComponent(params[key]) + "&";
    }
    url = url.substring(0, url.length - 1);

    var page = require('webpage').create();
    page.open(url, function () {
        var response = page.plainText;
        if (response.replace(/\s*/g, "")) {
            JSON.parse(response);
        }

        page.close();

        done(null, response);
    });
};

exports.TestingEnvironment = new TestingEnvironment();
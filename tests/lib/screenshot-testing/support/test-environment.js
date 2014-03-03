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
    this.reload();
};

TestingEnvironment.prototype.reload = function () {
    if (fs.exists(testingEnvironmentOverridePath)) {
        var data = JSON.parse(fs.read(testingEnvironmentOverridePath));
        for (var key in data) {
            this[key] = data[key];
        }
    }
};

TestingEnvironment.prototype.save = function () {
    fs.write(testingEnvironmentOverridePath, JSON.stringify(this));

    console.log("Saving TestEnvironment vars to " + testingEnvironmentOverridePath);
    console.log("  -> " + JSON.stringify(this));
    console.log(" A-> " + fs.read(testingEnvironmentOverridePath));
};

TestingEnvironment.prototype.callApi = function (method, params, done) {
    params.module = "API";
    params.method = method;
    params.format = 'json';

    this._call(params, done);
};

TestingEnvironment.prototype.callController = function (method, params, done) {
    var parts = method.split('.');

    params.module = parts[0];
    params.action = parts[1];
    params.idSite = params.idSite || 1;

    this._call(params, done);
};

TestingEnvironment.prototype._call = function (params, done) {
    var url = path.join(config.piwikUrl, "tests/PHPUnit/proxy/index.php?");
    for (var key in params) {
        var value = params[key];
        if (value instanceof Array) {
            for (var i = 0; i != value.length; ++i) {
                url += key + "[]=" + encodeURIComponent(value[i]) + "&";
            }
        } else {
            url += key + "=" + encodeURIComponent(value) + "&";
        }
    }
    url = url.substring(0, url.length - 1);

    var page = require('webpage').create();
    page.open(url, function () {
        var response = page.plainText;
        if (response.replace(/\s*/g, "")) {
            try {
                response = JSON.parse(response);
            } catch (e) {
                throw new Error("Unable to parse JSON response: " + response);
            }

            if (response.result == "error") {
                throw new Error("API returned error: " + response.message);
            }
        }

        page.close();

        done(null, response);
    });
};

exports.TestingEnvironment = new TestingEnvironment();
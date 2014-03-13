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
    for (var key in this) {
        delete this[key];
    }

    if (fs.exists(testingEnvironmentOverridePath)) {
        var data = JSON.parse(fs.read(testingEnvironmentOverridePath));
        for (var key in data) {
            this[key] = data[key];
        }
    }
};

TestingEnvironment.prototype.save = function () {
    var copy = {};
    for (var key in this) {
        copy[key] = this[key];
    }

    fs.write(testingEnvironmentOverridePath, JSON.stringify(copy));
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
                done(new Error("Unable to parse JSON response: " + response));
                return;
            }

            if (response.result == "error") {
                done(new Error("API returned error: " + response.message));
                return;
            }
        }

        page.close();

        done(null, response);
    });
};

TestingEnvironment.prototype.setupFixture = function (fixtureClass, done) {
    console.log("    Setting up fixture " + fixtureClass + "...");

    var setupFile = path.join("./support", "setupDatabase.php"),
        processArgs = [setupFile, "--server=" + JSON.stringify(config.phpServer), "--fixture=" + (fixtureClass || "")];

    if (options['persist-fixture-data']) {
        processArgs.push('--persist-fixture-data');
    }

    if (options['drop']) {
        processArgs.push('--drop');
    }

    var child = require('child_process').spawn(config.php, processArgs);

    child.stdout.on("data", function (data) {
        fs.write("/dev/stdout", data, "w");
    });

    child.stderr.on("data", function (data) {
        fs.write("/dev/stderr", data, "w");
    });

    child.on("exit", function (code) {
        if (code) {
            done(new Error("Failed to setup fixture " + fixtureClass + " (error code = " + code + ")"));
        } else {
            done();
        }
    });
};

TestingEnvironment.prototype.teardownFixture = function (fixtureClass, done) {
    if (options['persist-fixture-data']
        || !fixtureClass
    ) {
        this.deleteAndSave();

        done();
        return;
    }

    console.log("    Tearing down fixture " + fixtureClass + "...");

    var teardownFile = path.join("./support", "teardownDatabase.php"),
        child = require('child_process').spawn(
            config.php, [teardownFile, "--server=" + JSON.stringify(config.phpServer), "--fixture=" + fixtureClass]);

    child.stdout.on("data", function (data) {
        fs.write("/dev/stdout", data, "w");
    });

    child.stderr.on("data", function (data) {
        fs.write("/dev/stderr", data, "w");
    });

    var self = this;
    child.on("exit", function (code) {
        self.deleteAndSave();

        if (code) {
            done(new Error("Failed to teardown fixture " + fixtureClass + " (error code = " + code + ")"));
        } else {
            done();
        }
    });
};

TestingEnvironment.prototype.deleteAndSave = function () {
    fs.write(testingEnvironmentOverridePath, "{}");
    this.reload();
};

exports.TestingEnvironment = new TestingEnvironment();
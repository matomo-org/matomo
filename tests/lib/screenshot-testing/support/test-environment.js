/*!
 * Piwik - free/libre analytics platform
 *
 * Test environment overriding
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    testingEnvironmentOverridePath = path.join(PIWIK_INCLUDE_PATH, '/tmp/testingPathOverride.json');

var DEFAULT_UI_TEST_FIXTURE_NAME = "Piwik\\Tests\\Fixtures\\UITestFixture";

var TestingEnvironment = function () {
    this.reload();
};

TestingEnvironment.prototype.reload = function () {
    for (var key in this) {
        delete this[key];
    }

    this['useOverrideCss'] = true;
    this['useOverrideJs'] = true;
    this['loadRealTranslations'] = true; // UI tests should test w/ real translations, not translation keys
    this['testUseMockAuth'] = true;
    this['configOverride'] = {};

    if (fs.exists(testingEnvironmentOverridePath)) {
        var data = JSON.parse(fs.read(testingEnvironmentOverridePath));
        for (var key in data) {
            this[key] = data[key];
        }
    }
};

/**
 * Overrides a config entry.
 *
 * You can use this method either to set one specific config value `overrideConfig(group, name, value)`
 * or you can set a whole group of values `overrideConfig(group, valueObject)`.
 */
TestingEnvironment.prototype.overrideConfig = function (group, name, value) {
    if (!name) {
        return;
    }

    if (!this['configOverride']) {
        this['configOverride'] = {};
    }

    if ((typeof value) === 'undefined') {
        this['configOverride'][group] = name;
        return;
    }

    if (!this['configOverride'][group]) {
        this['configOverride'][group] = {};
    }

    this['configOverride'][group][name] = value;
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
                page.close();

                done(new Error("Unable to parse JSON response: " + response));
                return;
            }

            if (response.result == "error") {
                page.close();

                done(new Error("API returned error: " + response.message));
                return;
            }
        }

        page.close();

        done(null, response);
    });
};

TestingEnvironment.prototype.executeConsoleCommand = function (command, args, callback) {
    var consoleFile = path.join(PIWIK_INCLUDE_PATH, 'console'),
        commandArgs = [consoleFile, command].concat(args),
        child = require('child_process').spawn(config.php, commandArgs);

    var firstLine = true;
    child.stdout.on("data", function (data) {
        if (firstLine) {
            data = "    " + data;
            firstLine = false;
        }

        fs.write("/dev/stdout", data.replace(/\n/g, "\n    "), "w");
    });

    child.stderr.on("data", function (data) {
        if (firstLine) {
            data = "    " + data;
            firstLine = false;
        }

        fs.write("/dev/stderr", data, "w");
    });

    child.on("exit", callback);
};

TestingEnvironment.prototype.addPluginOnCmdLineToTestEnv = function () {
    if (options.plugin) {
        this.pluginsToLoad = [options.plugin];
        this.save();
    }
};

var droppedOnce = false;
TestingEnvironment.prototype.setupFixture = function (fixtureClass, done) {
    console.log("    Setting up fixture " + fixtureClass + "...");

    this.deleteAndSave();

    var args = [
        fixtureClass || DEFAULT_UI_TEST_FIXTURE_NAME,
        '--set-phantomjs-symlinks',
        '--server-global=' + JSON.stringify(config.phpServer)
    ];

    if (options['persist-fixture-data']) {
        args.push('--persist-fixture-data');
    }

    if (options['drop']
        && !droppedOnce
    ) {
        args.push('--drop');
        droppedOnce = true;
    }

    if (options['plugin']) {
        args.push('--plugins=' + options['plugin']);
    }

    var self = this;
    this.executeConsoleCommand('tests:setup-fixture', args, function (code) {
        self.reload();
        self.addPluginOnCmdLineToTestEnv();

        self.fixtureClass = fixtureClass;
        self.save();

        console.log();

        if (code) {
            done(new Error("Failed to setup fixture " + fixtureClass + " (error code = " + code + ")"));
        } else {
            done();
        }
    });
};

TestingEnvironment.prototype.readDbInfoFromConfig = function () {

    var username = 'root';
    var password = '';

    var pathConfigIni = path.join(PIWIK_INCLUDE_PATH, "/config/config.ini.php");

    var configFile = fs.read(pathConfigIni);

    if (configFile) {
        var match = ('' + configFile).match(/password\s?=\s?"(.*)"/);

        if (match && match.length) {
            password = match[1];
        }

        match = ('' + configFile).match(/username\s?=\s?"(.*)"/);

        if (match && match.length) {
            username = match[1];
        }
    }

    return {
        username: username,
        password: password
    }
};

TestingEnvironment.prototype.teardownFixture = function (fixtureClass, done) {
    if (options['persist-fixture-data']
        || !fixtureClass
    ) {
        done();
        return;
    }

    console.log();
    console.log("    Tearing down fixture " + fixtureClass + "...");

    var args = [fixtureClass || DEFAULT_UI_TEST_FIXTURE_NAME, "--teardown", '--server-global=' + JSON.stringify(config.phpServer)];
    this.executeConsoleCommand('tests:setup-fixture', args, function (code) {
        if (code) {
            done(new Error("Failed to teardown fixture " + fixtureClass + " (error code = " + code + ")"));
        } else {
            done();
        }
    })
};

TestingEnvironment.prototype.deleteAndSave = function () {
    fs.write(testingEnvironmentOverridePath, "{}");
    this.reload();
};

exports.TestingEnvironment = new TestingEnvironment();

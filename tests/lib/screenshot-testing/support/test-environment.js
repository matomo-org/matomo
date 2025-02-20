/*!
 * Matomo - free/libre analytics platform
 *
 * Test environment overriding
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    path = require('path'),
    resolveUrl = require('url').resolve,
    request = require('request-promise'),
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
    this['optionsOverride'] = {};
    this['environmentVariables'] = {};

    if (fs.existsSync(testingEnvironmentOverridePath)) {
        var data = JSON.parse(fs.readFileSync(testingEnvironmentOverridePath));
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

    fs.writeFileSync(testingEnvironmentOverridePath, JSON.stringify(copy));
};

TestingEnvironment.prototype.callApi = function (method, params) {
    params.module = "API";
    params.method = method;
    params.format = 'json';

    return this._call(params);
};

TestingEnvironment.prototype.callController = function (method, params) {
    var parts = method.split('.');

    params.module = parts[0];
    params.action = parts[1];
    params.idSite = params.idSite || 1;

    return this._call(params);
};

TestingEnvironment.prototype._call = async function (params) {
    let queryString = Object.keys(params).reduce(function (obj, name) {
        if (params[name] instanceof Array) {
            params[name].forEach(function(value, index) {
                obj[name+'['+index+']'] = value;
            });
            return obj;
        }
        obj[name] = params[name];
        return obj;
    }, {});
    let response = await request({
        uri: resolveUrl(config.piwikUrl, '/tests/PHPUnit/proxy/index.php'),
        qs: queryString,
    });

    if (response === '') {
        return '';
    }

    response = response.replace(/\s*/g, "");

    try {
        response = JSON.parse(response);
    } catch (e) {
        throw new Error("Unable to parse JSON response: " + response + " for query " + JSON.stringify(queryString));
    }

    if (response.result === "error") {
        throw new Error("API returned error: " + response.message + " for query " + JSON.stringify(queryString));
    }

    return response;
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

        process.stdout.write(data.toString().replace(/\n/g, "\n    "));
    });

    child.stderr.on("data", function (data) {
        if (firstLine) {
            data = "    " + data;
            firstLine = false;
        }

        process.stderr.write(data);
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
        '--set-symlinks',
        '--server-global=' + JSON.stringify(config.phpServer),
        '-vvv',
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

    if (options['matomo-domain']) {
        args.push('--matomo-domain=' + options['matomo-domain']);
    }

    if (options['enable-logging']) {
        args.push('--enable-logging');
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
    let username = 'root';
    let password = '';

    const pathConfigIni = path.join(PIWIK_INCLUDE_PATH, "/config/config.ini.php");
    const configFile = fs.readFileSync(pathConfigIni);

    if (configFile) {
        let match;

        match = ('' + configFile).match(/password\s?=\s?"(.*)"/);

        if (match && match.length) {
            password = match[1];
        }

        match = ('' + configFile).match(/username\s?=\s?"(.*)"/);

        if (match && match.length) {
            username = match[1];
        }
    }

    return { username, password };
};

TestingEnvironment.prototype.readInstallationInfoFromLocalConfig = function () {
    let inProgress = false;
    let firstAccessed = null;

    const pathConfigIni = path.join(testEnvironment.configFileLocal);
    const configFile = fs.readFileSync(pathConfigIni);

    if (configFile) {
        let match;

        match = ('' + configFile).match(/installation_first_accessed\s?=\s?(\d+)/);

        if (match && match.length) {
            firstAccessed = parseInt(match[1]);
        }

        match = ('' + configFile).match(/installation_in_progress\s?=\s?(\d+)/);

        if (match && match.length) {
            inProgress = !!match[1];
        }
    }

    return { firstAccessed, inProgress };
};

TestingEnvironment.prototype.appendToLocalConfig = function (content) {
    const pathConfigIni = path.join(testEnvironment.configFileLocal);

    fs.appendFileSync(pathConfigIni, content);
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

    if (options['matomo-domain']) {
        args.push('--matomo-domain=' + options['matomo-domain']);
    }

    this.executeConsoleCommand('tests:setup-fixture', args, function (code) {
        if (code) {
            done(new Error("Failed to teardown fixture " + fixtureClass + " (error code = " + code + ")"));
        } else {
            done();
        }
    })
};

TestingEnvironment.prototype.deleteAndSave = function () {
    fs.writeFileSync(testingEnvironmentOverridePath, "{}");
    this.reload();
};

exports.TestingEnvironment = new TestingEnvironment();

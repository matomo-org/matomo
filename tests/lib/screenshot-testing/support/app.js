/*!
 * Piwik - Web Analytics
 *
 * UI screenshot test runner Application class
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    path = require('./path'),
    DiffViewerGenerator = require('./diff-viewer').DiffViewerGenerator

var walk = function (dir, pattern, result) {
    result = result || [];

    fs.list(dir).forEach(function (item) {
        if (item == '.'
            || item == '..'
        ) {
            return;
        }

        var wholePath = path.join(dir, item);

        if (fs.isDirectory(wholePath)) {
            walk(wholePath, pattern, result);
        } else if (wholePath.match(pattern)) {
            result.push(wholePath);
        }
    });

    return result;
};

var Application = function () {
    this.runner = null;
    this.diffViewerGenerator = new DiffViewerGenerator(path.join(uiTestsDir, config.screenshotDiffDir));
};

Application.prototype.printHelpAndExit = function () {
    console.log("Usage: phantomjs run-tests.js [options] [test-files]");
    console.log();
    console.log("Available options:");
    console.log("  --help:                 Prints this message.");
    console.log("  --persist-fixture-data: Persists test data in a database and does not execute tear down.");
    console.log("                          After the first run, the database setup will not be called, which");
    console.log("                          Makes running tests faster.");
    console.log("  --keep-symlinks:        If supplied, the recursive symlinks created in tests/PHPUnit/proxy");
    console.log("                          aren't deleted after tests are run. Specify this option if you'd like");
    console.log("                          to view pages phantomjs captures in a browser.");
    console.log("  --print-logs:           Prints webpage logs even if tests succeed.");

    phantom.exit(0);
};

Application.prototype.init = function () {
    var app = this;

    // overwrite describe function so we can inject the base directory of a suite
    var oldDescribe = describe;
    describe = function () {
        var suite = oldDescribe.apply(null, arguments);
        suite.baseDirectory = app.currentModulePath.match(/\/plugins\//) ? path.dirname(app.currentModulePath) : uiTestsDir;
        return suite;
    };
};

Application.prototype.loadTestModules = function () {
    var self = this,
        pluginDir = path.join(PIWIK_INCLUDE_PATH, 'plugins');

    // find all installed plugins
    var plugins = fs.list(pluginDir).map(function (item) {
        return path.join(pluginDir, item);
    }).filter(function (path) {
        return fs.isDirectory(path) && !path.match(/\/\.*$/);
    });

    // load all UI tests we can find
    var modulePaths = walk(uiTestsDir, /_spec\.js$/);

    plugins.forEach(function (pluginPath) {
        walk(path.join(pluginPath, 'tests'), /_spec\.js$/, modulePaths);
    });

    modulePaths.forEach(function (path) {
        self.currentModulePath = path;

        require(path);
    });

    // filter suites to run
    if (options.tests.length) {
        mocha.suite.suites = mocha.suite.suites.filter(function (suite) {
            return options.tests.indexOf(suite.title) != -1;
        });
    }
};

Application.prototype.runTests = function () {
    var self = this;

    // make sure all necessary directories exist (symlinks handled by PHP since phantomjs can't create any)
    var dirsToCreate = [
        config.expectedScreenshotsDir,
        config.processedScreenshotsDir,
        config.screenshotDiffDir,
        path.join(PIWIK_INCLUDE_PATH, 'tmp/sessions')
    ];

    dirsToCreate.forEach(function (path) {
        if (!fs.isDirectory(path)) {
            fs.makeTree(path);
        }
    });

    // remove existing diffs
    fs.list(config.screenshotDiffDir).forEach(function (item) {
        var file = path.join(uiTestsDir, config.screenshotDiffDir, item);
        if (fs.exists(file)
            && item.slice(-4) == '.png'
        ) {
            fs.remove(file);
        }
    });

    this.setupDatabase();
};

Application.prototype.setupDatabase = function () {
    console.log("Setting up database...");

    var self = this,
        setupFile = path.join("./support", "setupDatabase.php"),
        processArgs = [setupFile, "--server=" + JSON.stringify(config.phpServer)];

    if (options['persist-fixture-data']) {
        processArgs.push('--persist-fixture-data');
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
            console.log("\nERROR: Failed to setup database!");
            phantom.exit(-1);
        } else {
            self.doRunTests();
        }
    });
};

Application.prototype.doRunTests = function () {
    var self = this;

    // run tests
    this.runner = mocha.run(function () {
        // remove symlinks
        if (!options['keep-symlinks']) {
            var symlinks = ['libs', 'plugins', 'tests'];

            symlinks.forEach(function (item) {
                var file = path.join(uiTestsDir, '..', 'proxy', item);
                if (fs.exists(file)) {
                    fs.remove(file);
                }
            });
        }

        // build diffviewer
        self.diffViewerGenerator.checkImageMagickCompare(function () {
            self.diffViewerGenerator.generate(function () {
                if (options['persist-fixture-data']) {
                    self.finish();
                } else {
                    // teardown database
                    self.tearDownDatabase();
                }
            });
        });
    });
};

Application.prototype.tearDownDatabase = function () {
    console.log("Tearing down database...");

    var self = this,
        teardownFile = path.join("./support", "teardownDatabase.php"),
        child = require('child_process').spawn(config.php, [teardownFile, "--server=" + JSON.stringify(config.phpServer)]);

    child.stdout.on("data", function (data) {
        fs.write("/dev/stdout", data, "w");
    });

    child.stderr.on("data", function (data) {
        fs.write("/dev/stderr", data, "w");
    });

    child.on("exit", function (code) {
        if (code) {
            console.log("\nERROR: Failed to teardown database!");
            phantom.exit(-2);
        } else {
            self.finish();
        }
    });
};

Application.prototype.finish = function () {
    phantom.exit(this.runner.failures);
};

exports.Application = new Application();
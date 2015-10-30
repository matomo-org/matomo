/*!
 * Piwik - free/libre analytics platform
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

var isCorePlugin = function (pathToPlugin) {
    // if the plugin is a .git checkout, it's not part of core
    var gitDir = path.join(pathToPlugin, '.git');
    return !fs.exists(gitDir);
};

var Application = function () {
    this.runner = null;

    this.diffviewerDir = path.join(PIWIK_INCLUDE_PATH, 'tests/UI', config.screenshotDiffDir);
    this.diffViewerGenerator = new DiffViewerGenerator(this.diffviewerDir);
};

Application.prototype.printHelpAndExit = function () {
    console.log("Usage: phantomjs run-tests.js [options] [test-files]");
    console.log();
    console.log("Available options:");
    console.log("  --help:                   Prints this message.");
    console.log("  --persist-fixture-data:   Persists test data in a database and does not execute tear down.");
    console.log("                            After the first run, the database setup will not be called, which");
    console.log("                            Makes running tests faster.");
    console.log("  --plugin=name:            Runs all tests for a plugin.");
    console.log("  --keep-symlinks:          If supplied, the recursive symlinks created in tests/PHPUnit/proxy");
    console.log("                            aren't deleted after tests are run. Specify this option if you'd like");
    console.log("                            to view pages phantomjs captures in a browser.");
    console.log("  --print-logs:             Prints webpage logs even if tests succeed.");
    console.log("  --store-in-ui-tests-repo: Stores processed screenshots within the UI tests repository even if");
    console.log("                            the tests are in another plugin. For use with travis build.");
    console.log("  --assume-artifacts:       Assume the diffviewer and processed screenshots will be stored on the.");
    console.log("                            builds artifacts server. For use with travis build.");
    console.log("  --screenshot-repo:        Specifies the github repository that contains the expected screenshots");
    console.log("                            to link to in the diffviewer. For use with travis build.");
    console.log("  --core:                   Only execute UI tests that are for Piwik core or Piwik core plugins.");
    console.log("  --first-half:             Only execute first half of all the test suites. Will be only applied if no")
    console.log("                            specific plugin or test-files requested");
    console.log("  --second-half:            Only execute second half of all the test suites. Will be only applied if no")
    console.log("                            specific plugin or test-files requested");

    phantom.exit(0);
};

Application.prototype.init = function () {
    var app = this;

    // overwrite describe function so we can inject the base directory of a suite
    var oldDescribe = describe;
    describe = function () {
        var suite = oldDescribe.apply(null, arguments);
        suite.baseDirectory = app.currentModulePath.match(/\/plugins\//) ? path.dirname(app.currentModulePath) : uiTestsDir;
        if (options['assume-artifacts']) {
            suite.diffDir = path.join(PIWIK_INCLUDE_PATH, 'tests/UI', config.screenshotDiffDir);
        } else {
            suite.diffDir = path.join(suite.baseDirectory, config.screenshotDiffDir);
        }
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

    if (options.core) {
        plugins = plugins.filter(function (path) {
            return isCorePlugin(path);
        });
    }

    plugins.forEach(function (pluginPath) {
        walk(path.join(pluginPath, 'Test'), /_spec\.js$/, modulePaths);
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

    if (options.plugin) {
        mocha.suite.suites = mocha.suite.suites.filter(function (suite) {
            return suite.baseDirectory.match(new RegExp("\/plugins\/" + options.plugin + "\/"));
        });
    }

    var specificTestsRequested = options.plugin || options.tests.length;

    if ((options['run-first-half-only'] || options['run-second-half-only']) && !specificTestsRequested) {
        // run only first 50% of the test suites or only run last 50% of the test suites.
        // we apply this option only if not a specific plugin or test suite was requested. Only there for travis to
        // split tests into multiple jobs.
        var numTestsFirstHalf = Math.round(mocha.suite.suites.length / 2);
        numTestsFirstHalf += 5; // run a few more test suits in first half as UiIntegrationTests contain many tests
        mocha.suite.suites = mocha.suite.suites.filter(function (suite, index) {
            if (options['run-first-half-only'] && index < numTestsFirstHalf) {
                return true;
            } else if (options['run-second-half-only'] && index >= numTestsFirstHalf) {
                return true;
            }
            return false;
        });
    }

    if (!mocha.suite.suites.length) {
        console.log("No tests are executing... are you running tests for a plugin? Make sure to use the"
                  + " --plugin=MyPlugin option.");
    }

    // configure suites (auto-add fixture setup/teardown)
    mocha.suite.suites.forEach(function (suite) {
        var fixture = typeof suite.fixture === 'undefined' ? "Piwik\\Tests\\Fixtures\\UITestFixture" : suite.fixture;

        suite.beforeAll(function (done) {
            var oldOptions = JSON.parse(JSON.stringify(options));
            if (suite.optionsOverride) {
                for (var key in suite.optionsOverride) {
                    options[key] = suite.optionsOverride[key];
                }
            }

            // remove existing diffs
            fs.list(suite.diffDir).forEach(function (item) {
                var file = path.join(suite.diffDir, item);
                if (fs.exists(file)
                    && item.slice(-4) == '.png'
                ) {
                    fs.remove(file);
                }
            });

            testEnvironment.setupFixture(fixture, done);

            options = oldOptions;
        });

        // move to before other hooks
        suite._beforeAll.unshift(suite._beforeAll.pop());

        suite.afterAll(function (done) {
            var oldOptions = JSON.parse(JSON.stringify(options));
            if (suite.optionsOverride) {
                for (var key in suite.optionsOverride) {
                    options[key] = suite.optionsOverride[key];
                }
            }

            testEnvironment.teardownFixture(fixture, done);

            options = oldOptions;
        });
    });
};

Application.prototype.runTests = function () {
    var self = this;

    // make sure all necessary directories exist (symlinks handled by PHP since phantomjs can't create any)
    var dirsToCreate = [
        path.join(PIWIK_INCLUDE_PATH, 'tmp/sessions')
    ];

    dirsToCreate.forEach(function (path) {
        if (!fs.isDirectory(path)) {
            fs.makeTree(path);
        }
    });

    this.doRunTests();
};

Application.prototype.doRunTests = function () {
    var self = this;

    testEnvironment.reload();

    // run tests
    this.runner = mocha.run(function () {
        // remove symlinks
        if (!options['keep-symlinks']) {
            var symlinks = ['libs', 'plugins', 'tests', 'misc', 'piwik.js'];

            symlinks.forEach(function (item) {
                var file = path.join(uiTestsDir, '..', 'PHPUnit', 'proxy', item);
                if (fs.exists(file)) {
                    fs.remove(file);
                }
            });
        }

        // build diffviewer
        self.diffViewerGenerator.generate(function () {
            self.finish();
        });
    });

    this.runner.on('fail', function(test, err) {
        var indent = "     ";

        var message = "\n";
        message += err && err.stack ? err.stack : err;
        message = message.replace(/\n/g, "\n" + indent);
        console.log(indent + message + "\n\n");
    });
};

Application.prototype.finish = function () {
    phantom.exit(this.runner ? this.runner.failures : -1);
};

Application.prototype.appendMissingExpected = function (screenName) {
    var missingExpectedFilePath = path.join(this.diffviewerDir, 'missing-expected.list');
    fs.write(missingExpectedFilePath, screenName + "\n", "a");
};

exports.Application = new Application();
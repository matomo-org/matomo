/*!
 * Matomo - free/libre analytics platform
 *
 * UI screenshot test runner Application class
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    fsExtra = require('fs-extra'),
    path = require('./path');

var walk = function (dir, pattern, result) {
    result = result || [];

    if (!fs.isDirectory(dir)) {
        return result;
    }

    fs.readdirSync(dir).forEach(function (item) {
        if (item === '.'
            || item === '..'
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
    return !fs.existsSync(gitDir);
};

var hasSpecialNeeds = function (pathToPlugin) {
    // skip plugins that have special needs in core build
    var actionFile = path.join(pathToPlugin, '.github/workflows/matomo-tests.yml');
    if (!fs.existsSync(actionFile)) {
        return false;
    }
    var action = fs.readFileSync(actionFile);
    return /setup-script:/.test(action);
};

var Application = function () {
    this.runner = null;

    this.diffviewerDir = path.join(PIWIK_INCLUDE_PATH, 'tests/UI', config.screenshotDiffDir);
};

Application.prototype.printHelpAndExit = function () {
    console.log("Usage: node run-tests.js [options] [test-files]");
    console.log();
    console.log("Available options:");
    console.log("  --help:                   Prints this message.");
    console.log("  --persist-fixture-data:   Persists test data in a database and does not execute tear down.");
    console.log("                            After the first run, the database setup will not be called, which");
    console.log("                            Makes running tests faster.");
    console.log("  --plugin=name:            Runs all tests for a plugin.");
    console.log("  --keep-symlinks:          If supplied, the recursive symlinks created in tests/PHPUnit/proxy");
    console.log("                            aren't deleted after tests are run. Specify this option if you'd like");
    console.log("                            to view pages puppeteer captures in a browser.");
    console.log("  --print-logs:             Prints webpage logs even if tests succeed.");
    console.log("  --store-in-ui-tests-repo: Stores processed screenshots within the UI tests repository even if");
    console.log("                            the tests are in another plugin. For use with CI build.");
    console.log("  --assume-artifacts:       Assume the diffviewer and processed screenshots will be stored on the.");
    console.log("                            builds artifacts server. For use with CI build.");
    console.log("  --screenshot-repo:        Specifies the GitHub repository that contains the expected screenshots");
    console.log("                            to link to in the diffviewer. For use with CI build.");
    console.log("  --core:                   Only execute UI tests that are for Matomo core or Matomo core plugins.");
    console.log("  --num-test-groups:        Divide all test execution into this many overall groups. Use --test-group to pick which group to run in this execution.");
    console.log("  --test-group:             The test group to run.");

    process.exit(0);
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

        // remove existing diffs
        if (!fs.existsSync(suite.diffDir)) {
            fs.mkdirSync(suite.diffDir);
        }

        fs.readdirSync(suite.diffDir).forEach(function (item) {
            var file = path.join(suite.diffDir, item);
            if (fs.existsSync(file)
                && item.slice(-4) === '.png'
            ) {
                fs.unlinkSync(file);
            }
        });

        return suite;
    };
};

Application.prototype.loadTestModules = function () {
    var self = this,
        pluginDir = path.join(PIWIK_INCLUDE_PATH, 'plugins');

    // find all installed plugins
    var plugins = fs.readdirSync(pluginDir).map(function (item) {
        return path.join(pluginDir, item);
    }).filter(function (path) {
        return fs.isDirectory(path) && !path.match(/\/\.*$/);
    });

    // load all UI tests we can find
    var modulePaths = walk(uiTestsDir, /_spec\.js$/);

    if (options.core && !options['store-in-ui-tests-repo']) {
        plugins = plugins.filter(function (path) {
            return isCorePlugin(path);
        });
    }

    if (!options.plugin) {
        plugins = plugins.filter(function (path) {
            return !hasSpecialNeeds(path);
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

    if (options['num-test-groups'] && options['test-group'] && !specificTestsRequested) {
        // run only N% of the test suites.
        // we apply this option only if not a specific plugin or test suite was requested.
        // Only there for CI to split tests into multiple jobs.

        var numberOfGroupsToSplitTestsInto = parseInt(options['num-test-groups']);
        var testGroupToRun = parseInt(options['test-group']);

        mocha.suite.suites = mocha.suite.suites.filter(function (suite, index) {
            return index % numberOfGroupsToSplitTestsInto === testGroupToRun;
        });
    }

    if (!mocha.suite.suites.length) {
        console.log("No tests are executing... are you running tests for a plugin? Make sure to use the"
                  + " --plugin=MyPlugin option.");
    }

    // configure suites (auto-add fixture setup/teardown)
    mocha.suite.suites.forEach(function (suite) {
        var fixture = typeof suite.fixture === 'undefined' ? "Piwik\\Tests\\Fixtures\\UITestFixture" : suite.fixture;

        suite.beforeAll(async function () {
            await page.createPage();
        });

        suite.beforeAll(function (done) {
            this.timeout(10*60*1000); // 10 mins timeout for fixture setup

            var oldOptions = JSON.parse(JSON.stringify(options));
            if (suite.optionsOverride) {
                for (var key in suite.optionsOverride) {
                    options[key] = suite.optionsOverride[key];
                }
            }

            testEnvironment.setupFixture(fixture, (error, result) => {
                options = oldOptions;

                done(error, result);
            });
        });

        // move in front of other beforeAll hooks (called twice as we're adding two beforeAll handlers)
        suite._beforeAll.unshift(suite._beforeAll.pop());
        suite._beforeAll.unshift(suite._beforeAll.pop());

        suite.afterAll(function (done) {
            this.timeout(10*60*1000); // 10 mins timeout for fixture teardown

            var oldOptions = JSON.parse(JSON.stringify(options));
            if (suite.optionsOverride) {
                for (var key in suite.optionsOverride) {
                    options[key] = suite.optionsOverride[key];
                }
            }

            testEnvironment.teardownFixture(fixture, (error, result) => {
                options = oldOptions;

                done(error, result);
            });
        });

        // if a test fails, print failure info and for non-comparison fails, save failure screenshot
        suite.afterEach(async function() {
            const test = this.currentTest;
            const err = this.currentTest && this.currentTest.err;
            if (!err) {
                return;
            }

            var indent = "     ";

            var message = err && err.message ? err.message : err;
            if (message.indexOf(indent) !== 0) {
                message = indent + message.replace(/\n/g, "\n" + indent);
            }

            const url = await page.getWholeCurrentUrl();
            message += "\n" + indent + indent + "Url to reproduce: " + url + "\n";

            if (message.indexOf('Generated screenshot') === -1) {

                var processedPath = path.join(PIWIK_INCLUDE_PATH, 'tests/UI/processed-ui-screenshots');

                if (options.plugin) {
                    processedPath = path.join(PIWIK_INCLUDE_PATH, 'plugins', options.plugin, 'tests/UI/processed-ui-screenshots');
                }

                if (!fs.existsSync(processedPath)) {
                    fsExtra.mkdirsSync(processedPath);
                }
                const failurePath = path.join(processedPath, test.title.replace(/(\s|[^a-zA-Z0-9_])+/g, '_') + '_failure.png');

                message += indent + indent + "Screenshot of failure: " + failurePath + "\n";

                const screenshot = await page.screenshot({ fullPage: true });
                fs.writeFileSync(failurePath, screenshot);
            } else {
                delete this.currentTest.err.stack;
            }

            var renderingLogs = page.getPageLogsString(indent);
            if (renderingLogs) {
                message += renderingLogs + "\n";
            } else {
                message += indent + indent + "No captured console logs.\n";
            }

            console.log(message); // so it prints out as the test fails (for builds that run too long)
            this.currentTest.err.message = message.replace(/\n/g, "\n  ");
        });
    });
};

Application.prototype.runTests = function (mocha) {
    // make sure all necessary directories exist (symlinks handled by PHP since puppeteer can't create any)
    var dirsToCreate = [
        path.join(PIWIK_INCLUDE_PATH, 'tmp/sessions')
    ];

    dirsToCreate.forEach(function (path) {
        if (!fs.isDirectory(path)) {
            fsExtra.mkdirsSync(path);
        }
    });

    this.doRunTests(mocha);
};

Application.prototype.doRunTests = function (mocha) {
    testEnvironment.reload();

    // run tests
    this.runner = mocha.run(function (failures) {
        // remove symlinks
        if (!options['keep-symlinks']) {
            var symlinks = ['libs', 'plugins', 'tests', 'misc', 'node_modules', 'piwik.js', 'matomo.js'];

            symlinks.forEach(function (item) {
                var file = path.join(uiTestsDir, '..', 'PHPUnit', 'proxy', item);
                if (fs.existsSync(file)) {
                    fs.unlinkSync(file);
                }
            });
        }
    });

    this.runner.on('test', function () {
        page._reset();
    });

    this.runner.on('end', function() {
      // we are terminating but we are waiting for all other events to finish
      setTimeout(() => process.exit(this.failures), 10000);
    })
};

Application.prototype.appendMissingExpected = function (screenName) {
    var missingExpectedFilePath = path.join(this.diffviewerDir, 'missing-expected.list');
    fs.appendFileSync(missingExpectedFilePath, screenName + "\n");
};

exports.Application = new Application();

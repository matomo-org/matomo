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
    console.log("  --core-tests-only:        Only execute UI tests from tests/UI and skip plugin UI specs.");
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

    if (options.core && !options.plugin) {
        plugins = plugins.filter(function (path) {
            return isCorePlugin(path);
        });
    }

    if (!options.plugin) {
        plugins = plugins.filter(function (path) {
            return !hasSpecialNeeds(path);
        });
    }

    if (!options['core-tests-only']) {
        plugins.forEach(function (pluginPath) {
            walk(path.join(pluginPath, 'Test'), /_spec\.js$/, modulePaths);
            walk(path.join(pluginPath, 'tests'), /_spec\.js$/, modulePaths);
        });
    }

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
            this.timeout(0); // no timeout for fixture setup (this requires normal anonymous function, not fat arrow function)

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
            this.timeout(0); // no timeout for fixture teardown (this requires normal anonymous function, not fat arrow function)

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

    this.runner.on('end', function () {
        process.exitCode = this.failures;

        // Read off the merged config rather than a flag decided in config.dist.js: run-tests.js
        // layers tests/UI/config.js over the defaults, so a local override can turn the reporter
        // on or off and a flag settled in the defaults would not follow it. reporterEnabled only
        // names reporters when mocha-multi-reporters is the one running, and the merge is shallow,
        // so an override that sets reporter alone leaves the defaults' reporterOptions behind it -
        // reading that list unconditionally would take the wait for a run using only `spec`.
        const reporter = config.reporter;
        const namesTestomatio = (value) => typeof value === 'string'
            && value.indexOf('@testomatio/reporter') !== -1;
        const usesTestomatioReporter = namesTestomatio(reporter)
            || (reporter === 'mocha-multi-reporters'
                && namesTestomatio((config.reporterOptions || {}).reporterEnabled));

        // Nothing after this event needs the browser - the Testomatio adapter only reports run
        // status over HTTP - so close it on both paths and let Chrome go. Report a failure rather
        // than swallowing it: a close that rejects leaves the loop open, and it is the one thing
        // that would explain the forced exit below.
        page.browser.close().catch((e) => {
            console.log('Failed to close the browser: ' + (e && e.message ? e.message : e));
        });

        // The Testomatio reporter keeps sending API requests after this event, so when it is
        // active we still have to wait for it (#21760). Without it there is nothing left to
        // flush, and the wait is dead time on every run. Closing the browser above cannot shorten
        // this wait, because the timer is ref'd.
        if (usesTestomatioReporter) {
            setTimeout(() => process.exit(), 10000);
            return;
        }

        // Safety net only, and unref'd so it cannot itself delay the exit. The window covers
        // everything still pending after this event, not just the close above: measured end-to-
        // exit at 108ms on UsersManager, the suite whose after() hooks call testEnvironment's
        // fetch-based API helper right before the run ends, so an idle keep-alive socket is
        // included in that number. It has to announce itself, or a silent firing
        // would quietly bring back both the truncation and the ten seconds with nothing to notice
        // - so exit from the write's callback rather than the same tick, because stdout is
        // asynchronous when it is a pipe (CI, or any `| tee`) and process.exit() discards pending
        // writes. The inner timer is only there so a wedged pipe cannot hang the run instead.
        setTimeout(() => {
            setTimeout(() => process.exit(), 1000).unref();
            process.stdout.write(
                'Forcing exit: something is still holding the event loop open after the run.\n',
                () => process.exit(),
            );
        }, 5000).unref();
    })
};

Application.prototype.appendMissingExpected = function (screenName) {
    var missingExpectedFilePath = path.join(this.diffviewerDir, 'missing-expected.list');
    fs.appendFileSync(missingExpectedFilePath, screenName + "\n");
};

exports.Application = new Application();

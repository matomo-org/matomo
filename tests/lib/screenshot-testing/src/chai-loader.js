/*!
 * Piwik - free/libre analytics platform
 *
 * chai assertion extensions
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    path = require('path'),
    AssertionError = chai.AssertionError;

function ChaiLoader(pageRenderer, app, config) {
    this.pageRenderer = pageRenderer;
    this.uiTestsDir = path.join(PIWIK_INCLUDE_PATH, 'tests', 'UI');
    this.app = app;
    this.config = config;
}

ChaiLoader.prototype.initExtras = function () {
    this.setupKeywords();
    this.addChaiAssertions();
};

ChaiLoader.prototype.setupKeywords = function () {
    var self = this;

    // add screenshot keyword to `expect`
    expect.screenshot = function (file, prefix) {
        if (!prefix) {
            prefix = self.app.runner.suite.title; // note: runner is made global by run-tests.js
        }

        return chai.expect(prefix + '_' + file);
    };

    // add page keyword to `expect`
    expect.page = function (url) {
        return chai.expect(url);
    };

    expect.current_page = expect.page(null);
};

ChaiLoader.prototype.addChaiAssertions = function () {
    var self = this;

    chai.Assertion.addChainableMethod('captureSelector', function () {
        var compareAgainst = this.__flags['object'];

        if (arguments.length == 3) {
            var screenName  = compareAgainst,
                selector    = arguments[0],
                pageSetupFn = arguments[1],
                done        = arguments[2];
        } else {
            var screenName  = self.app.runner.suite.title + "_" + arguments[0],
                selector    = arguments[1],
                pageSetupFn = arguments[2],
                done        = arguments[3];
        }

        self.capture(screenName, compareAgainst, selector, pageSetupFn, done);
    });

    chai.Assertion.addChainableMethod('capture', function () {
        var compareAgainst = this.__flags['object'];

        if (arguments.length == 2) {
            var screenName  = compareAgainst,
                pageSetupFn = arguments[0],
                done        = arguments[1];
        } else {
            var screenName  = self.app.runner.suite.title + "_" + arguments[0],
                pageSetupFn = arguments[1],
                done        = arguments[2];
        }

        self.capture(screenName, compareAgainst, null, pageSetupFn, done);
    });

    // add `contains` assertion
    chai.Assertion.addChainableMethod('contains', function () {
        var url = this.__flags['object']
            ;

        if (arguments.length == 3) {
            var elementSelector = arguments[0],
                pageSetupFn = arguments[1],
                screenName = null,
                done = arguments[2];
        } else {
            var elementSelector = arguments[0],
                screenName = self.app.runner.suite.title + "_" + arguments[1],
                pageSetupFn = arguments[2],
                done = arguments[3];
        }

        self.checkContains(elementSelector, screenName, pageSetupFn, done);
    });
};

ChaiLoader.prototype.checkContains = function (elementSelector, screenName, pageSetupFn, done) {
    var self = this;

    if (url !== null
        && url !== undefined
        && this.pageRenderer.getCurrentUrl() !== url
    ) {
        this.pageRenderer.load(url);
    }

    pageSetupFn(this.pageRenderer);

    if (!(done instanceof Function)) {
        throw new Error("No 'done' callback specified in 'contains' assertion.");
    }

    var capturePath = screenName ? this.getProcessedScreenshotPath(screenName) : null;

    this.pageRenderer.capture(capturePath, function (err) {
        var indent = "     ";

        if (err) {
            err.stack = err.message + "\n" + indent + self.getPageLogsString(self.pageRenderer.pageLogs, indent);

            done(err);
            return;
        }

        try {
            self.assert(
                self.pageRenderer.contains(elementSelector),
                "Expected page to contain element '" + elementSelector + "', but could not find it in page.",
                "Expected page to not contain element '" + elementSelector + "', but found it in page."
            );

            done();
        } catch (originalError) {
            var stack = originalError.message + "\n\n";
            if (capturePath) {
                stack += indent + "View the captured screenshot at '" + capturePath + "'.";
            } else {
                stack += indent + "NOTE: No screenshot name was supplied to this '.contains(' assertion. If the second argument is a screenshot name, "
                + "the screenshot will be saved so you can debug this failure.";
            }

            stack += self.getPageLogsString(self.pageRenderer.pageLogs, indent);

            var error = new AssertionError(originalError.message);
            error.stack = stack;

            done(error);
        }
    });
};

ChaiLoader.prototype.capture = function(screenName, compareAgainst, selector, pageSetupFn, done) {
    if (!(done instanceof Function)) {
        throw new Error("No 'done' callback specified in capture assertion.");
    }

    var screenshotFileName = screenName + '.png',
        dirsBase = this.app.runner.suite.baseDirectory,

        expectedScreenshotDir = path.join(dirsBase, this.config.expectedScreenshotsDir),
        expectedScreenshotPath = path.join(expectedScreenshotDir, compareAgainst + '.png'),

        processedScreenshotPath = this.getProcessedScreenshotPath(screenName);

    screenshotDiffDir = path.join(options['store-in-ui-tests-repo'] ? this.uiTestsDir : dirsBase, this.config.screenshotDiffDir);

    if (!fs.isDir(screenshotDiffDir)) {
        fs.makeTree(screenshotDiffDir);
    }

    pageSetupFn(this.pageRenderer);

    try {
        var self = this;
        this.pageRenderer.capture(processedScreenshotPath, function (err) {
            if (err) {
                var indent = "     ";
                err.stack = err.message + "\n" + indent + self.getPageLogsString(self.pageRenderer.pageLogs, indent);

                done(err);
                return;
            }

            var testInfo = {
                name: screenName,
                processed: fs.isFile(processedScreenshotPath) ? processedScreenshotPath : null,
                expected: fs.isFile(expectedScreenshotPath) ? expectedScreenshotPath : null,
                baseDirectory: dirsBase
            };

            var fail = function (message) {
                self.app.diffViewerGenerator.failures.push(testInfo);

                var expectedPath = testInfo.expected ? path.resolve(testInfo.expected) :
                        (expectedScreenshotPath + " (not found)"),
                    processedPath = testInfo.processed ? path.resolve(testInfo.processed) :
                        (processedScreenshotPath + " (not found)");

                var indent = "     ";
                var failureInfo = message + "\n";
                failureInfo += indent + "Url to reproduce: " + self.pageRenderer.getCurrentUrl() + "\n";
                failureInfo += indent + "Generated screenshot: " + processedPath + "\n";
                failureInfo += indent + "Expected screenshot: " + expectedPath + "\n";

                failureInfo += self.getPageLogsString(self.pageRenderer.pageLogs, indent);

                error = new AssertionError(message);

                // stack traces are useless so we avoid the clutter w/ this
                error.stack = failureInfo;

                done(error);
            };

            var pass = function () {
                if (options['print-logs']) {
                    console.log(self.getPageLogsString(self.pageRenderer.pageLogs, "     "));
                }

                done();
            };

            if (!testInfo.processed) {
                fail("Failed to generate screenshot to " + screenshotFileName + ".");
                return;
            }

            if (!testInfo.expected) {
                fail("No expected screenshot found for " + screenshotFileName + ".");
                return;
            }

            var expected = fs.read(expectedScreenshotPath),
                processed = fs.read(processedScreenshotPath);

            if (processed == expected) {
                pass();
                return;
            }

            // if the files are not exact, perform a diff to check if they are truly different
            resemble("file://" + processedScreenshotPath).compareTo("file://" + expectedScreenshotPath).onComplete(function(data) {
                if (data.misMatchPercentage != 0) {
                    fail("Processed screenshot does not match expected for " + screenshotFileName + ". (mismatch = " + data.misMatchPercentage + ")");
                    return;
                }

                pass();
            });
        }, selector);
    } catch (ex) {
        var err = new Error(ex.message);
        err.stack = ex.message;
        done(err);
    }
};

ChaiLoader.prototype.getProcessedScreenshotPath = function(screenName) {
    var screenshotFileName = screenName + '.png',
        dirsBase = this.app.runner.suite.baseDirectory,
        processedScreenshotDir = path.join(options['store-in-ui-tests-repo'] ? this.uiTestsDir : dirsBase, this.config.processedScreenshotsDir);

    if (!fs.isDir(processedScreenshotDir)) {
        fs.makeTree(processedScreenshotDir);
    }

    return path.join(processedScreenshotDir, screenshotFileName);
};

ChaiLoader.prototype.getPageLogsString = function(pageLogs, indent) {
    var result = "";
    if (pageLogs.length) {
        result = "\n\n" + indent + "Rendering logs:\n";
        pageLogs.forEach(function (message) {
            result += indent + "  " + message.replace(/\n/g, "\n" + indent + "  ") + "\n";
        });
        result = result.substring(0, result.length - 1);
    }
    return result;
};

exports.ChaiLoader = ChaiLoader;
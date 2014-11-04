/*!
 * Piwik - free/libre analytics platform
 *
 * chai assertion extensions
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    PageRenderer = require('./page-renderer.js').PageRenderer,
    AssertionError = chai.AssertionError;

// add screenshot keyword to `expect`
expect.screenshot = function (file, prefix) {
    if (!prefix) {
        prefix = app.runner.suite.title; // note: runner is made global by run-tests.js
    }

    return chai.expect(prefix + '_' + file);
};

// add page keyword to `expect`
expect.page = function (url) {
    return chai.expect(url);
};

expect.current_page = expect.page(null);

function getPageLogsString(pageLogs, indent) {
    var result = "";
    if (pageLogs.length) {
        result = "\n\n" + indent + "Rendering logs:\n";
        pageLogs.forEach(function (message) {
            result += indent + "  " + message.replace(/\n/g, "\n" + indent + "  ") + "\n";
        });
        result = result.substring(0, result.length - 1);
    }
    return result;
}

// add capture assertion
var pageRenderer = new PageRenderer(path.join(config.piwikUrl, "tests", "PHPUnit", "proxy"));

function capture(screenName, compareAgainst, selector, pageSetupFn, done) {

    if (!(done instanceof Function)) {
        throw new Error("No 'done' callback specified in capture assertion.");
    }

    var screenshotFileName = screenName + '.png',
        dirsBase = app.runner.suite.baseDirectory,

        expectedScreenshotDir = path.join(dirsBase, config.expectedScreenshotsDir),
        expectedScreenshotPath = path.join(expectedScreenshotDir, compareAgainst + '.png'),

        processedScreenshotDir = path.join(options['store-in-ui-tests-repo'] ? uiTestsDir : dirsBase, config.processedScreenshotsDir),
        processedScreenshotPath = path.join(processedScreenshotDir, screenshotFileName),

        screenshotDiffDir = path.join(options['store-in-ui-tests-repo'] ? uiTestsDir : dirsBase, config.screenshotDiffDir);

    if (!fs.isDirectory(processedScreenshotDir)) {
        fs.makeTree(processedScreenshotDir);
    }

    if (!fs.isDirectory(screenshotDiffDir)) {
        fs.makeTree(screenshotDiffDir);
    }

    pageSetupFn(pageRenderer);

    try {
        pageRenderer.capture(processedScreenshotPath, function (err) {
            if (err) {
                var indent = "     ";
                err.stack = err.message + "\n" + indent + getPageLogsString(pageRenderer.pageLogs, indent);

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
                app.diffViewerGenerator.failures.push(testInfo);

                var expectedPath = testInfo.expected ? path.resolve(testInfo.expected) :
                        (expectedScreenshotPath + " (not found)"),
                    processedPath = testInfo.processed ? path.resolve(testInfo.processed) :
                        (processedScreenshotPath + " (not found)");

                var indent = "     ";
                var failureInfo = message + "\n";
                failureInfo += indent + "Url to reproduce: " + pageRenderer.getCurrentUrl() + "\n";
                failureInfo += indent + "Generated screenshot: " + processedPath + "\n";
                failureInfo += indent + "Expected screenshot: " + expectedPath + "\n";

                failureInfo += getPageLogsString(pageRenderer.pageLogs, indent);

                error = new AssertionError(message);

                // stack traces are useless so we avoid the clutter w/ this
                error.stack = failureInfo;

                done(error);
            };

            var pass = function () {
                if (options['print-logs']) {
                    console.log(getPageLogsString(pageRenderer.pageLogs, "     "));
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
}

chai.Assertion.addChainableMethod('captureSelector', function () {
    var compareAgainst = this.__flags['object'];

    if (arguments.length == 3) {
        var screenName  = compareAgainst,
            selector    = arguments[0],
            pageSetupFn = arguments[1],
            done        = arguments[2];
    } else {
        var screenName  = app.runner.suite.title + "_" + arguments[0],
            selector    = arguments[1],
            pageSetupFn = arguments[2],
            done        = arguments[3];
    }

    capture(screenName, compareAgainst, selector, pageSetupFn, done);
});

chai.Assertion.addChainableMethod('capture', function () {
    var compareAgainst = this.__flags['object'];

    if (arguments.length == 2) {
        var screenName  = compareAgainst,
            pageSetupFn = arguments[0],
            done        = arguments[1];
    } else {
        var screenName  = app.runner.suite.title + "_" + arguments[0],
            pageSetupFn = arguments[1],
            done        = arguments[2];
    }

    capture(screenName, compareAgainst, null, pageSetupFn, done);
});

// add `contains` assertion
chai.Assertion.addChainableMethod('contains', function () {
    var self = this,
        url = this.__flags['object'],
        elementSelector = arguments[0],
        pageSetupFn = arguments[1],
        done = arguments[2];

    if (url
        && pageRenderer.getCurrentUrl() != url
    ) {
        pageRenderer.load(url);
    }

    pageSetupFn(pageRenderer);

    if (!(done instanceof Function)) {
        throw new Error("No 'done' callback specified in 'contains' assertion.");
    }

    pageRenderer.capture(null, function (err) {
        var obj = self._obj,
            indent = "     ";

        if (err) {
            err.stack = err.message + "\n" + indent + getPageLogsString(pageRenderer.pageLogs, indent);

            done(err);
            return;
        }

        try {
            self.assert(
                pageRenderer.contains(elementSelector),
                "Expected page to contain element '" + elementSelector + "', but could not find it in page.",
                "Expected page to not contain element '" + elementSelector + "', but found it in page."
            );

            done();
        } catch (error) {
            error.stack = getPageLogsString(pageRenderer.pageLogs, indent);
            done(error);
        }
    });
});
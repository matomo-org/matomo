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

var testsToIgnoreIfAborted = [];

function shouldTestBeSkippedOnAbort(screenName){

    for (var i in testsToIgnoreIfAborted) {
        // we skip test if needed but still upload the screenshot for the diff just a few lines further up
        if (testsToIgnoreIfAborted[i] + '.png' === screenName) {

            return true;
        }
    }

    return false;
}

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

// add file keyword to `expect`
expect.file = expect.screenshot;

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
var pageRenderer = new PageRenderer(config.piwikUrl + path.join("tests", "PHPUnit", "proxy"));

function getExpectedScreenshotPath() {

    if (typeof config.expectedScreenshotsDir == 'string') {
        config.expectedScreenshotsDir = [config.expectedScreenshotsDir];
    }
    for (var dir in config.expectedScreenshotsDir) {
        var expectedScreenshotDir = path.join(app.runner.suite.baseDirectory, config.expectedScreenshotsDir[dir]);
        if (fs.isDirectory(expectedScreenshotDir)) {
            break;
        }
    }

    return expectedScreenshotDir;
}

function getExpectedFilePath(fileName) {

    fileName = assumeFileIsImageIfNotSpecified(fileName);

    return path.join(getExpectedScreenshotPath(), fileName);
}

function getProcessedFilePath(fileName) {
    var pathToUITests = options['store-in-ui-tests-repo'] ? uiTestsDir : app.runner.suite.baseDirectory;
    var processedScreenshotDir = path.join(pathToUITests, config.processedScreenshotsDir);

    if (!fs.isDirectory(processedScreenshotDir)) {
        fs.makeTree(processedScreenshotDir);
    }
    fileName = assumeFileIsImageIfNotSpecified(fileName);

    return path.join(processedScreenshotDir, fileName);
}

function assumeFileIsImageIfNotSpecified(filename) {
    if(!endsWith(filename, '.png') && !endsWith(filename, '.txt') ) {
        return filename + '.png';
    }
    return filename;
}

function endsWith(string, needle)
{
    return string.substr(-1 * needle.length, needle.length) === needle;
}


function failCapture(fileTypeString, pageRenderer, testInfo, expectedFilePath, processedFilePath, message, done) {

    app.diffViewerGenerator.failures.push(testInfo);

    var expectedPath = testInfo.expected ? path.resolve(testInfo.expected) :
            (expectedFilePath + " (not found)"),
        processedPath = testInfo.processed ? path.resolve(testInfo.processed) :
            (processedFilePath + " (not found)");

    var indent = "     ";
    var failureInfo = message + "\n";
    failureInfo += indent + "Url to reproduce: " + pageRenderer.getCurrentUrl() + "\n";
    failureInfo += indent + "Generated " + fileTypeString + ": " + processedPath + "\n";
    failureInfo += indent + "Expected " + fileTypeString + ": " + expectedPath + "\n";

    failureInfo += getPageLogsString(pageRenderer.pageLogs, indent);

    error = new AssertionError(message);

    // stack traces are useless so we avoid the clutter w/ this
    error.stack = failureInfo;

    done(error);
}

function getScreenshotDiffDir() {
    return path.join(options['store-in-ui-tests-repo'] ? uiTestsDir : app.runner.suite.baseDirectory, config.screenshotDiffDir);
}

function capture(screenName, compareAgainst, selector, pageSetupFn, comparisonThreshold, done) {

    if (!(done instanceof Function)) {
        throw new Error("No 'done' callback specified in capture assertion.");
    }

    screenName = assumeFileIsImageIfNotSpecified(screenName);
    compareAgainst = assumeFileIsImageIfNotSpecified(compareAgainst);

    var expectedScreenshotPath = getExpectedFilePath(compareAgainst),
        processedScreenshotPath = getProcessedFilePath(screenName),
        screenshotDiffDir = getScreenshotDiffDir();

    if (!fs.isDirectory(screenshotDiffDir)) {
        fs.makeTree(screenshotDiffDir);
    }

    pageSetupFn(pageRenderer);

    try {
        pageRenderer.capture(processedScreenshotPath, function (err) {
            if (err) {
                var indent = "     ";
                err.stack = err.message + "\n" + indent + getPageLogsString(pageRenderer.pageLogs, indent);

                if (shouldTestBeSkippedOnAbort(screenName)) {
                    console.log('SKIPPING TEST ' + screenName + ' AS IT RANDOMLY GETS ABORTED:');
                    console.log(err.stack);
                    done();
                    return;
                }

                done(err);
                return;
            }

            var testInfo = {
                name: screenName,
                processed: fs.isFile(processedScreenshotPath) ? processedScreenshotPath : null,
                expected: fs.isFile(expectedScreenshotPath) ? expectedScreenshotPath : null,
                baseDirectory: app.runner.suite.baseDirectory
            };

            var fail = function (message) {
                failCapture("screenshot", pageRenderer, testInfo, expectedScreenshotPath, processedScreenshotPath, message, done);
            };

            var pass = function () {
                if (options['print-logs']) {
                    console.log(getPageLogsString(pageRenderer.pageLogs, "     "));
                }

                done();
            };

            if (!testInfo.processed) {
                fail("Failed to generate screenshot to " + screenName + ".");
                return;
            }

            if (!testInfo.expected) {
                app.appendMissingExpected(screenName);

                fail("No expected screenshot found for " + screenName + ".");
                return;
            }

            function screenshotMatches(misMatchPercentage) {
                if (comparisonThreshold) {
                    return misMatchPercentage <= 100 * (1 - comparisonThreshold);
                } else {
                    return misMatchPercentage == 0;
                }
            }

            function compareImages(expected, processed)
            {
                var args = ["-metric", "AE", expected, processed, 'null:'];
                var child = require('child_process').spawn('compare', args);

                var testFailure = '';

                function onCommandResponse (numPxDifference) {
                    // on success we get numPxDifference = '0' meaning no pixel was different
                    // on different images we get the number of different pixels
                    // on any error we get an error message (eg image size different)
                    numPxDifference = numPxDifference.trim();

                    if (numPxDifference && numPxDifference !== '0') {
                        if (/^(\d+)$/.test(numPxDifference)) {
                            testFailure += "(" + numPxDifference + "px difference";
                        } else {
                            testFailure += "(image magick error: " + numPxDifference;
                        }

                        testFailure += ")\n";
                    }
                }

                child.stdout.on("data", onCommandResponse);
                child.stderr.on("data", onCommandResponse);

                child.on("exit", function (code) {
                    if (testFailure) {
                        testFailure = 'Processed screenshot does not match expected for ' + screenName + ' ' + testFailure;
                        testFailure += 'TestEnvironment was ' + JSON.stringify(testEnvironment);
                    }

                    if (code == 0 && !testFailure) {
                        pass();
                    } else if (comparisonThreshold) {
                        // we use image magick only for exact match comparison, if there is a threshold we now check if this one fails
                        resemble("file://" + processedScreenshotPath).compareTo("file://" + expectedScreenshotPath).onComplete(function(data) {
                            if (!screenshotMatches(data.misMatchPercentage)) {
                                fail(testFailure + ". (mismatch = " + data.misMatchPercentage + ")");
                                return;
                            }

                            pass();
                        });
                    } else {
                        fail(testFailure);
                    }
                });
            }

            compareImages(expectedScreenshotPath, processedScreenshotPath);

        }, selector);
    } catch (ex) {
        var err = new Error(ex.message);
        err.stack = ex.message;
        done(err);
    }
}

function compareContents(compareAgainst, pageSetupFn, done) {
    if (!(done instanceof Function)) {
        throw new Error("No 'done' callback specified in 'pageContents' assertion.");
    }

    compareAgainst = assumeFileIsImageIfNotSpecified(compareAgainst);

    var screenshotDiffDir = getScreenshotDiffDir(),
        processedFilePath = getProcessedFilePath(compareAgainst),
        expectedFilePath = getExpectedFilePath(compareAgainst);


    if (!fs.isDirectory(screenshotDiffDir)) {
        fs.makeTree(screenshotDiffDir);
    }

    pageSetupFn(pageRenderer);


    try {
        pageRenderer.capture(processedFilePath, function (err) {
            if (err) {
                var indent = "     ";
                err.stack = err.message + "\n" + indent + getPageLogsString(pageRenderer.pageLogs, indent);

                done(err);
                return;
            }

            var fail = function (message) {
                failCapture("file", pageRenderer, testInfo, expectedFilePath, processedFilePath, message, done);
            };

            var pass = function () {
                if (options['print-logs']) {
                    console.log(getPageLogsString(pageRenderer.pageLogs, "     "));
                }

                done();
            };

            var processed = pageRenderer.getPageContents();

            fs.write(processedFilePath, processed);

            var filename = processedFilePath.split(/[\\/]/).pop();
            var testInfo = {
                name: filename,
                processed: fs.isFile(processedFilePath) ? processedFilePath : null,
                expected: fs.isFile(expectedFilePath) ? expectedFilePath : null,
                baseDirectory: app.runner.suite.baseDirectory
            };

            if (!fs.isFile(testInfo.expected)) {
                fail("No expected output file found at " + testInfo.expected + ".");
                return;
            }

            var expected = fs.read(testInfo.expected);

            if (processed == expected) {
                pass();
            } else {
                fail("Processed page contents does not equal expected file contents.");
            }
        });
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

    var comparisonThreshold = this.__flags['comparisonThreshold'];

    capture(screenName, compareAgainst, selector, pageSetupFn, comparisonThreshold, done);
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

    var comparisonThreshold = this.__flags['comparisonThreshold'];

    capture(screenName, compareAgainst, null, pageSetupFn, comparisonThreshold, done);
});

chai.Assertion.addChainableMethod('skippedOnAbort', function () {
    var compareAgainst = this.__flags['object'];
    testsToIgnoreIfAborted.push(compareAgainst);
});

// add `contains` assertion
chai.Assertion.addChainableMethod('contains', function () {
    var self = this,
        url = this.__flags['object']
        ;

    if (arguments.length == 3) {
        var elementSelector = arguments[0],
            pageSetupFn = arguments[1],
            screenName = null,
            done = arguments[2];
    } else {
        var elementSelector = arguments[0],
            screenName = app.runner.suite.title + "_" + arguments[1],
            pageSetupFn = arguments[2],
            done = arguments[3];
    }

    if (url !== null
        && url !== undefined
        && pageRenderer.getCurrentUrl() !== url
    ) {
        pageRenderer.load(url);
    }

    pageSetupFn(pageRenderer);

    if (!(done instanceof Function)) {
        throw new Error("No 'done' callback specified in 'contains' assertion.");
    }

    var capturePath = screenName ? getProcessedFilePath(screenName) : null;

    pageRenderer.capture(capturePath, function (err) {
        var indent = "     ";

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
        } catch (originalError) {
            var stack = originalError.message + "\n\n";
            if (capturePath) {
                stack += indent + "View the captured screenshot at '" + capturePath + "'.";
            } else {
                stack += indent + "NOTE: No screenshot name was supplied to this '.contains(' assertion. If the second argument is a screenshot name, "
                + "the screenshot will be saved so you can debug this failure.";
            }

            stack += getPageLogsString(pageRenderer.pageLogs, indent);

            var error = new AssertionError(originalError.message);
            error.stack = stack;

            done(error);
        }
    });
});

chai.Assertion.addChainableMethod('similar', function (comparisonThreshold) {
    if (comparisonThreshold === null
        || comparisonThreshold === undefined
    ) {
        throw new Error("No comparison threshold supplied to '.similar('!");
    }

    this.__flags['comparisonThreshold'] = comparisonThreshold;
});

// add pageContents assertion
chai.Assertion.addChainableMethod('pageContents', function (pageSetupFn, done) {
    var compareAgainst = this.__flags['object'];

    compareContents(compareAgainst, pageSetupFn, done);
});

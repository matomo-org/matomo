/*!
 * Matomo - free/libre analytics platform
 *
 * chai assertion extensions
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    fsExtra = require('fs-extra'),
    path = require('path'),
    chai = require('chai'),
    chaiFiles = require('chai-files'),
    AssertionError = chai.AssertionError;
const { spawnSync } = require('child_process');

/**
 * Returns a chai plugin that adds the `.matchImage` assertion.
 *
 * Usage:
 *
 * var baseFilePath = '...';
 * chai.use(require('chai-image-assert')(baseFilePath));
 *
 */
module.exports = function makeChaiImageAssert(comparisonCommand = 'compare') {
    return function chaiImageAssert(chai, utils) {
        chai.Assertion.addMethod('matchImage', matchImage);

        function matchImage(params) {
            if (typeof params === 'string') {
                params = { imageName: params };
            }

            let { imageName, compareAgainst, comparisonThreshold, prefix } = params;

            if (!prefix) {
                prefix = app.runner.suite.title; // note: runner is made global by run-tests.js
            }

            imageName = prefix + '_' + imageName;

            compareAgainst = compareAgainst || imageName;

            imageName = assumeFileIsImageIfNotSpecified(imageName);
            compareAgainst = assumeFileIsImageIfNotSpecified(compareAgainst);

            const expectedPath = getExpectedFilePath(compareAgainst),
                processedPath = getProcessedFilePath(imageName);

            const processedScreenshotsPath = path.dirname(processedPath);

            if (!fs.isDirectory(processedScreenshotsPath)) {
                fs.mkdirSync(processedScreenshotsPath);
            }

            const imageBuffer = this._obj;

            chai.assert.instanceOf(imageBuffer, Buffer);
            fs.writeFileSync(processedPath, imageBuffer);

            try {
                if (!fs.isFile(expectedPath)) {
                    app.appendMissingExpected(imageName);
                    this.assert(false, `expected file at '${expectedPath}' does not exist`);
                } else {
                    var matches = compareImages(expectedPath, processedPath, comparisonThreshold);

                    this.assert(
                        matches,
                        `expected screenshot to match ${expectedPath}`,
                        `expected screenshot to not match ${expectedPath}`
                    );

                    performAutomaticPageChecks();
                }
            } catch (e) {
                fail(e.message);
            }

            function fail(message) {
                var testInfo = {
                    name: imageName,
                    processed: fs.isFile(processedPath) ? processedPath : null,
                    expected: fs.isFile(expectedPath) ? expectedPath : null,
                    baseDirectory: app.runner.suite.baseDirectory
                };

                if (options['assume-artifacts']) {
                    const diffPath = getDiffPath(imageName);

                    // copy to diff dir for ui tests viewer (we don't generate diffs w/ compare since it slows the tests a bit)
                    if (!fs.existsSync(diffPath)) {
                        try {
                          fs.linkSync(expectedPath, diffPath);
                        } catch (e) {
                          console.log(`Failed to copy ${expectedPath} to ${diffPath}`);
                        }
                    }
                }

                var expectedPathStr = testInfo.expected ? path.resolve(testInfo.expected) : (expectedPath + " (not found)"),
                    processedPathStr = testInfo.processed ? path.resolve(testInfo.processed) : (processedPath + " (not found)");

                var indent = "     ";
                var failureInfo = message + "\n";
                failureInfo += indent + "Generated screenshot: " + processedPathStr + "\n";
                failureInfo += indent + "Expected screenshot: " + expectedPathStr + "\n";

                var error = new AssertionError(message);
                error.message = failureInfo;

                throw error;
            }
        }

        function compareImages(expectedPath, processedPath, comparisonThreshold) {
            const command = comparisonCommand,
                args = [
                    '-metric',
                    'ae',
                    expectedPath,
                    processedPath,
                    'null:'
                ];

            const result = spawnSync(command, args);

            chai.assert(!isCommandNotFound(result),
                `the '${comparisonCommand}' command was not found, ('compare' is provided by imagemagick)`);

            const allOutput = result.stdout.toString() + result.stderr.toString();
            const pixelError = (new Number(allOutput)).valueOf();

            chai.assert(!isNaN(pixelError),
                `the '${comparisonCommand}' command output could not be parsed, should be` +
                ` an integer, got: ${allOutput.replace(/\s+$/g, '')}`);

            if (pixelError === 0) {
                return true;
            }

            if (comparisonThreshold) {
                const { imageWidth, imageHeight } = getImageDimensions(expectedPath);
                const area = imageWidth * imageHeight;
                const percentDifference = pixelError / area;

                chai.assert(percentDifference <= comparisonThreshold, `images differ by ${(percentDifference * 100).toFixed(2)}%, `
                    + `which is greater than threshold ${(comparisonThreshold * 100).toFixed(2)}% (command output: ${allOutput.replace(/\s+$/g, '')})`);
                return true;
            }

            // allow a 10 pixel difference only
            chai.assert(pixelError <= 10, `images differ in ${pixelError} pixels (command output: ${allOutput.replace(/\s+$/g, '')})`);

            // if pixel error passes, but status is unexpected for some reason
            chai.assert(result.status === 0 || result.status === 1, `the '${comparisonCommand}' command returned a unexpected status: ${result.status}. Output was ${allOutput.replace(/\s+$/g, '')}`);

            return true;
        }

        function getImageDimensions(imagePath) {
            // NOTE: this method assumes 'identify' exists if 'compare' exists

            const commandArgs = [
                imagePath,
            ];

            const result = spawnSync('identify', commandArgs);
            const allOutput = (result.stdout || '').toString() + (result.stderr || '').toString();

            chai.assert(result.status === 0, `magick command failed, output: ${allOutput}`);

            const dimensions = allOutput.split(' ')[2];
            const [ imageWidth, imageHeight ] = dimensions.split('x');

            const dimsObj = {
                imageWidth: parseInt(imageWidth),
                imageHeight: parseInt(imageHeight),
            };

            chai.assert(!isNaN(dimsObj.imageWidth) && !isNaN(dimsObj.imageHeight),
                `Could not parse dimensions in magick output. Output: ${allOutput}`);

            return dimsObj;
        }
    };
};

expect.file = function (filename) {
    prefix = app.runner.suite.title; // note: runner is made global by run-tests.js
    filename = prefix + '_' + filename;

    return chai.expect(chaiFiles.file(getExpectedFilePath(filename)));
};

function isCommandNotFound(result) {
    return result.status === 127
        || (result.error != null && result.error.code === 'ENOENT');
}

function getExpectedScreenshotPath() {
    if (typeof config.expectedScreenshotsDir === 'string') {
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
        fsExtra.mkdirsSync(processedScreenshotDir);
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
    return needle.length === 0 || string.slice(-needle.length) === needle;
}

// other automatically run assertions
function performAutomaticPageChecks() {
    //checkForDangerousLinks();
}

function checkForDangerousLinks() {
    var links = page.webpage.evaluate(() => {
        try {
            var result = [];

            var linkElements = document.getElementsByTagName('a');
            for (var i = 0; i !== linkElements.length; ++i) {
                var element = linkElements.item(i);

                var href = element.getAttribute('href');
                if (/^(javascript|vbscript|data):/.test(href) && !isWhitelistedJavaScript(href)) {
                    result.push(element.innerText + ' - [href = ' + href + ']');
                }
            }

            return JSON.stringify(result);
        } catch (e) {
            return e.message || e;
        }

        function isWhitelistedJavaScript(href) {
            var whitelistedCode = [
                '',
                'void(0)',
                'window.history.back()',
                'window.location.reload()',
            ];

            var m = /^javascript:(.*?);*$/.exec(href);
            if (!m) {
                return false;
            }

            var code = m[1] || '';
            return whitelistedCode.indexOf(code) !== -1;
        }
    });
    expect(links, "found dangerous links").to.equal('{}');
}

function getDiffPath(testInfoName) {
    var baseDir = path.join(PIWIK_INCLUDE_PATH, 'tests/UI');
    return path.resolve(path.join(baseDir, config.screenshotDiffDir, testInfoName));
}

/*!
 * Piwik - Web Analytics
 *
 * Image diff & HTML diff viewer generation.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    path = require('./path');

var DiffViewerGenerator = function (diffDir) {
    this.diffDir = diffDir;
    this.outputPath = path.join(diffDir, 'diffviewer.html');
    this.failures = [];
    this.isCompareAvailable = true;
};

DiffViewerGenerator.prototype.checkImageMagickCompare = function (callback) {
    var self = this;

    var child = require('child_process').spawn('compare', '--help');
    child.on("exit", function (code) {
        self.isCompareAvailable = code == 0 || code == 1;

        if (!self.isCompareAvailable) {
            console.log("Cannot find ImageMagick compare utility, no diffs will be created.");
        }

        callback();
    });
};

DiffViewerGenerator.prototype.getDiffPath = function (testInfo) {
    var baseDir = options['assume-artifacts'] ? path.join(PIWIK_INCLUDE_PATH, 'tests/PHPUnit/UI') : testInfo.baseDirectory;
    return path.resolve(path.join(baseDir, config.screenshotDiffDir, testInfo.name + '.png'));
};

// TODO: diff output path shouldn't be stored in piwik-ui-tests repo
DiffViewerGenerator.prototype.getUrlForPath = function (path) {
    return fs.relpath(path, this.diffDir);
};

DiffViewerGenerator.prototype.generate = function (callback) {
    if (this.failures.length == 0) {
        return callback();
    }

    console.log("Generating diffs...");

    var self = this;
    this.generateDiffs(function () {
        var diffViewerContent = "<html>\
<head></head>\
<body>\
<h1>Screenshot Test Failures</h1>\
<table>\
    <tr>\
        <th>Name</th>\
        <th>Expected</th>\
        <th>Processed</th>\
        <th>Difference</th>\
    </tr>";

        for (var i = 0; i != self.failures.length; ++i) {
            var entry = self.failures[i];

            if (entry.expected) {
                var expectedUrl = self.getUrlForPath(entry.expected),
                    screenshotRepo = options['screenshot-repo'] || 'piwik/piwik-ui-tests',
                    pathPrefix = options['screenshot-repo'] ? '/Test/UI' : '',
                    expectedUrlGithub = 'https://raw.githubusercontent.com/' + screenshotRepo + '/master' + pathPrefix
                                      + '/expected-ui-screenshots/' + entry.name + '.png';

                var expectedHtml = '';
                if (!options['assume-artifacts']) {
                    expectedHtml += '<a href="' + expectedUrl + '">Expected</a>&nbsp;';
                }
                expectedHtml += '<a href="' + expectedUrlGithub + '">[Github]</a>';
            } else {
                var expectedHtml = '<em>Not found</em>';
            }

            if (entry.processed) {
                if (options['assume-artifacts']) {
                    entry.processedUrl = path.join("../processed-ui-screenshots", path.basename(entry.processed));
                } else {
                    entry.processedUrl = self.getUrlForPath(entry.processed);
                }
            }

            var entryLocationHint = '',
                hintSource = entry.expected || entry.processed,
                m = hintSource ? hintSource.match(/\/plugins\/([^\/]*)\//) : null;
            if (m) {
                entryLocationHint = ' <em>(for ' + m[1] + ' plugin)</em>';
            }

            diffViewerContent += '\
    <tr>\
        <td>' + entry.name + entryLocationHint + '</td>\
        <td>' + expectedHtml + '</td>\
        <td>' + (entry.processed ? ('<a href="' + entry.processedUrl + '">Processed</a>') : '<em>Not found</em>') + '</td>\
        <td>' + (entry.diffUrl ? ('<a href="' + entry.diffUrl + '">Difference</a>') : '<em>Could not create diff.</em>') + '</td>\
    </tr>';
        }

        diffViewerContent += '\
</table>\
</body>\
</html>';

        fs.write(self.outputPath, diffViewerContent, "w");

        console.log("Failures encountered. View all diffs at: " + self.outputPath);
        console.log();
        console.log("If processed screenshots are correct, you can copy the generated screenshots to the expected "
                  + "screenshot folder.");
        console.log();
        console.log("*** IMPORTANT *** In your commit message, explain the cause of the difference in rendering so other "
                  + "Piwik developers will be aware of it.");

        callback();
    });
};

DiffViewerGenerator.prototype.generateDiffs = function (callback, i) {
    i = i || 0;

    if (i >= this.failures.length
        || !this.isCompareAvailable
    ) {
        try {
            callback();
        } catch (ex) {
            console.error("Fatal error: failed to generate diffviewer: " + ex.stack);
            phantom.exit(-1);
        }
        return;
    }

    var entry = this.failures[i];

    if (entry.expected
        && entry.processed
    ) {
        var diffPath = this.getDiffPath(entry);

        var child = require('child_process').spawn('compare', [entry.expected, entry.processed, diffPath]);

        child.stdout.on("data", function (data) {
            fs.write("/dev/stdout", data, "w");
        });

        child.stderr.on("data", function (data) {
            fs.write("/dev/stderr", data, "w");
        });

        var self = this;
        child.on("exit", function (code) {
            if (!code) {
                console.log("Saved diff to " + diffPath);

                if (fs.exists(diffPath)) {
                    entry.diffUrl = entry.name + '.png';
                }
            }

            self.generateDiffs(callback, i + 1);
        });
    } else {
        this.generateDiffs(callback, i + 1);
    }
};

exports.DiffViewerGenerator = DiffViewerGenerator;
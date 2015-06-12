/*!
 * Piwik - free/libre analytics platform
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
};

DiffViewerGenerator.prototype.getDiffPath = function (testInfo) {
    var baseDir = path.join(PIWIK_INCLUDE_PATH, 'tests/UI');
    return path.resolve(path.join(baseDir, config.screenshotDiffDir, testInfo.name + '.png'));
};

// TODO: diff output path shouldn't be stored in piwik repo
DiffViewerGenerator.prototype.getUrlForPath = function (path) {
    return fs.relpath(path, this.diffDir);
};

DiffViewerGenerator.prototype.generate = function (callback) {
    if (this.failures.length == 0) {
        return callback();
    }

    console.log("Generating diff file");

    var diffViewerContent = "<html>\
<head></head>\
<body>\
<h1>Screenshot Test Failures</h1>\
<table>\
    <tr>\
        <th>Name</th>\
        <th>Expected</th>\
        <th>Expected Latest (Master)</th>\
        <th>Processed</th>\
        <th>Difference</th>\
    </tr>";

        for (var i = 0; i != this.failures.length; ++i) {
            var entry = this.failures[i];
            var expectedUrl = null;
            var githubUrl   = '';

            if (entry.expected) {
                if (options['assume-artifacts']) {
                    require('child_process').spawn('cp', [entry.expected, this.getDiffPath(entry)]);
                }

                var filename       = entry.name + '.png',
                    expectedUrl    = filename,
                    screenshotRepo = options['screenshot-repo'] || 'piwik/piwik-ui-tests',
                    pathPrefix     = options['screenshot-repo'] ? '/Test/UI' : '',
                    expectedUrlGithub = 'https://raw.githubusercontent.com/' + screenshotRepo + '/master' + pathPrefix
                                      + '/expected-ui-screenshots/' + filename;

                var expectedHtml = '';

                if (!options['assume-artifacts']) {
                    expectedUrl = this.getUrlForPath(entry.expected);
                }

                expectedHtml += '<a href="' + expectedUrl + '">Expected</a>&nbsp;';
                githubUrl     = '<a href="' + expectedUrlGithub + '">Github</a>';
            } else {
                var expectedHtml = '<em>Not found</em>';
            }

            if (entry.processed) {
                if (options['assume-artifacts']) {
                    entry.processedUrl = path.join("../processed-ui-screenshots", path.basename(entry.processed));
                } else {
                    entry.processedUrl = this.getUrlForPath(entry.processed);
                }
            }

            var entryLocationHint = '',
                hintSource = entry.expected || entry.processed,
                m = hintSource ? hintSource.match(/\/plugins\/([^\/]*)\//) : null;
            if (m) {
                entryLocationHint = ' <em>(for ' + m[1] + ' plugin)</em>';
            }

            var processedEntryPath = '';
            if (entry.processed) {
                processedEntryPath = path.basename(entry.processed);
            }

            diffViewerContent += '\
    <tr>\
        <td>' + entry.name + entryLocationHint + '</td>\
        <td>' + expectedHtml + '</td>\
        <td>' + githubUrl + '</td>\
        <td>' + (entry.processed ? ('<a href="' + entry.processedUrl + '">Processed</a>') : '<em>Not found</em>') + '</td>\
        <td>' + (expectedUrl ? ('<a href="singlediff.html?processed=' + entry.processedUrl + '&expected=' + expectedUrl + '&github=' + processedEntryPath + '">Difference</a>') : '<em>Could not create diff.</em>') + '</td>\
    </tr>';
        }

        diffViewerContent += '\
</table>\
</body>\
</html>';

        fs.write(this.outputPath, diffViewerContent, "w");

        console.log("Failures encountered. View all diffs at: " + this.outputPath);
        console.log();
        console.log("If processed screenshots are correct, you can copy the generated screenshots to the expected "
                  + "screenshot folder.");
        console.log();
        console.log("*** IMPORTANT *** In your commit message, explain the cause of the difference in rendering so other "
                  + "Piwik developers will be aware of it.");

        callback();
};

exports.DiffViewerGenerator = DiffViewerGenerator;
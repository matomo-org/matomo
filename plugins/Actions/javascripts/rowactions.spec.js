/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs');
var path = require('path');

(function () {
    describe('Actions row actions', function () {

        var overlayReport;

        beforeAll(async function () {
            var registered = [];

            window.DataTable_RowActions_Transitions = { registerReport: function () {} };
            window.DataTable_RowActions_Overlay = {
                registerReport: function (report) { registered.push(report); }
            };

            window.eval(fs.readFileSync(path.join(__dirname, 'rowactions.js'), 'utf8'));

            // the file registers its reports from a jQuery ready handler
            await new Promise(function (resolve) { setTimeout(resolve, 0); });

            overlayReport = registered.filter(function (report) {
                return report.isAvailableOnReport({ module: 'Actions', action: 'getPageUrls' });
            })[0];
        });

        /**
         * Builds a report row the way the reporting table does: _dataTableCell.twig writes the URL
         * into the href with one level of HTML escaping, which the parser removes again on read.
         */
        function linkForRow(url) {
            var escaped = url
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            var $row = $(
                '<table><tbody><tr><td>'
                + '<a href="' + escaped + '"><span class="label">row</span></a>'
                + '</td></tr></tbody></table>'
            ).find('tr');

            return overlayReport.onClick($('<a>'), $row, {}).link;
        }

        var urls = [
            'http://example.org/docs/manage-websites/',
            'http://example.org/index?a=1&b=2',
            // query parameters that look like named character references
            'http://example.org/index?a=1&copy=2&reg=3',
            // a literal &amp; in the tracked URL
            'http://example.org/index?a=1&amp;b=2',
        ];

        urls.forEach(function (url) {
            it('passes the row URL through unchanged: ' + url, function () {
                expect(linkForRow(url)).to.equal(url);
            });
        });
    });
})();

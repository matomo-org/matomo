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

            window.piwik.config = { action_url_category_delimiter: '/', action_title_category_delimiter: '' };
            window.eval(fs.readFileSync(path.join(__dirname, '../../CoreHome/javascripts/dataTable_rowactions.js'), 'utf8'));
            window.eval(fs.readFileSync(path.join(__dirname, '../../Transitions/javascripts/transitions.js'), 'utf8'));
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

        describe('Transitions', function () {

            var ALL_VISITS_AUGUST = { period: 'month', comparePeriods: '', date: '2026-08-01', compareDates: '' };
            var CHROME_SEPTEMBER = {
                segment: 'browserCode==CH',
                compareSegments: '',
                period: 'month',
                comparePeriods: '',
                date: '2026-09-01',
                compareDates: '',
            };

            /**
             * Builds a report row, and with paramOverride a comparison row under it the way
             * _dataTableViz_htmlTable_comparisons.twig renders it, with the series' segment (none for
             * "All visits"), period and date in data-param-override.
             */
            function buildRow(label, paramOverride) {
                var $table = $(
                    '<table><tbody><tr data-label="' + label + '">'
                    + '<td><a href="https://shop.example.org/about"><span class="label">' + label + '</span></a></td>'
                    + '</tr></tbody></table>'
                );
                if (!paramOverride) {
                    return $table.find('tr');
                }

                $table.find('tr').addClass('parentComparisonRow');
                var $row = $('<tr class="comparisonRow"><td class="label"><span class="label">series</span></td></tr>')
                    .attr('data-label', label)
                    .attr('data-param-override', JSON.stringify(paramOverride));
                $table.find('tbody').append($row);
                return $row;
            }

            /** Clicks Transitions on the row and returns what the popover opens. */
            function openedPopover(action, $row) {
                var rowAction = new DataTable_RowActions_Transitions({ param: { module: 'Actions', action: action } });

                var link = null;
                rowAction.openPopover = function (popoverLink) { link = popoverLink; };
                rowAction.trigger($row, new MouseEvent('click'));

                var opened = null;
                rowAction.openTransitionsPopover = function (actionType, actionName, overrideParams) {
                    opened = { actionType: actionType, actionName: actionName, overrideParams: overrideParams };
                };
                rowAction.doOpenPopover(link);

                return opened;
            }

            it('opens page URL transitions with no segment for an "All visits" comparison row', function () {
                expect(openedPopover('getPageUrls', buildRow('/about', ALL_VISITS_AUGUST))).to.deep.equal({
                    actionType: 'url',
                    actionName: 'https://shop.example.org/about',
                    overrideParams: { segment: '', period: 'month', date: '2026-08-01' },
                });
            });

            it('opens page URL transitions with the series segment for a compared segment row', function () {
                expect(openedPopover('getPageUrls', buildRow('/about', CHROME_SEPTEMBER)).overrideParams).to.deep.equal({
                    segment: 'browserCode==CH',
                    period: 'month',
                    date: '2026-09-01',
                });
            });

            it('opens page URL transitions with the report params on a row outside a comparison', function () {
                expect(openedPopover('getPageUrls', buildRow('/about', null)).overrideParams).to.deep.equal({});
            });

            it('opens page title transitions with no segment and the row period for an "All visits" comparison row', function () {
                expect(openedPopover('getPageTitles', buildRow('About us', ALL_VISITS_AUGUST))).to.deep.equal({
                    actionType: 'title',
                    actionName: 'About us',
                    overrideParams: { segment: '', period: 'month', date: '2026-08-01' },
                });
            });

            it('opens page title transitions with the series segment for a compared segment row', function () {
                expect(openedPopover('getPageTitles', buildRow('About us', CHROME_SEPTEMBER)).overrideParams).to.deep.equal({
                    segment: 'browserCode==CH',
                    period: 'month',
                    date: '2026-09-01',
                });
            });

            it('opens page title transitions with the report params on a row outside a comparison', function () {
                expect(openedPopover('getPageTitles', buildRow('About us', null))).to.deep.equal({
                    actionType: 'title',
                    actionName: 'About us',
                    overrideParams: {},
                });
            });
        });
    });
})();

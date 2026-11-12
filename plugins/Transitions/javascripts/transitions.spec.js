/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs');
var path = require('path');

(function () {
    describe('Transitions row action', function () {

        var rowAction;

        beforeAll(function () {
            window.DataTable_RowAction = function () {};
            window.DataTable_RowActions_Registry = {
                register: function (action) { rowAction = action; }
            };
            window._pk_translate = function (key) { return key; };
            window.piwik = {};

            window.eval(fs.readFileSync(path.join(__dirname, 'transitions.js'), 'utf8'));

            // as the Actions plugin registers its page URL and page title reports
            window.DataTable_RowActions_Transitions.registerReport({
                isAvailableOnReport: function (params) {
                    return params.module === 'Actions';
                }
            });
        });

        function isAvailable(action, period, maxPeriodAllowed, hasPreprocessedPages) {
            window.piwik.transitionsMaxPeriodAllowed = maxPeriodAllowed;
            window.piwik.transitionsHasPreprocessedPages = hasPreprocessedPages;

            return rowAction.isAvailableOnReport({ module: 'Actions', action: action, period: period });
        }

        it('is available within the period limit', function () {
            expect(isAvailable('getPageUrls', 'month', 'month', false)).to.equal(true);
            expect(isAvailable('getPageTitles', 'month', 'month', false)).to.equal(true);
        });

        it('is not available beyond the period limit without pre-processed pages', function () {
            expect(isAvailable('getPageUrls', 'year', 'month', false)).to.equal(false);
            expect(isAvailable('getEntryPageUrls', 'year', 'month', false)).to.equal(false);
        });

        it('is available beyond the period limit for page URLs when the site has pre-processed pages', function () {
            expect(isAvailable('getPageUrls', 'year', 'month', true)).to.equal(true);
            expect(isAvailable('getEntryPageUrls', 'year', 'day', true)).to.equal(true);
        });

        it('is not available beyond the period limit for page titles, which are never pre-processed', function () {
            expect(isAvailable('getPageTitles', 'year', 'month', true)).to.equal(false);
        });
    });
})();

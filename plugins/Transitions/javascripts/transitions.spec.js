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

        var originalCoreHome;

        beforeEach(function () {
            originalCoreHome = window.CoreHome;
        });

        afterEach(function () {
            window.CoreHome = originalCoreHome;
            delete window.piwik.transitionsMaxPeriodAllowed;
            delete window.piwik.transitionsHasPreprocessedPages;
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

        it('applies the period limit to ranges by their number of days', function () {
            window.CoreHome = Object.assign({}, originalCoreHome, {
                Periods: {
                    parse: function () {
                        return { getDayCount: function () { return 60; } };
                    }
                }
            });

            expect(isAvailable('getPageUrls', 'range', 'month', false)).to.equal(false);
            expect(isAvailable('getPageUrls', 'range', 'month', true)).to.equal(true);
            expect(isAvailable('getPageUrls', 'range', 'year', false)).to.equal(true);
        });

        it('keeps the period limit where the flag is not printed', function () {
            window.piwik.transitionsMaxPeriodAllowed = 'month';
            delete window.piwik.transitionsHasPreprocessedPages;

            expect(rowAction.isAvailableOnReport({ module: 'Actions', action: 'getPageUrls', period: 'year' })).to.equal(false);
        });
    });

    describe('Transitions model', function () {

        function loadPart(part, response) {
            var ajax = {
                callApi: function (method, params, callback) {
                    callback(response);
                }
            };
            var model = new window.Piwik_Transitions_Model(ajax);
            model.loadTotalNbPageviewsOnce = function () {};
            model.loadPart(part, 'url', 'http://example.org/', {}, function () {});

            return model;
        }

        it('records which parts were served from the stored data of a pre-processed page', function () {
            var model = loadPart('externalReferrers', {
                referrers: [],
                preprocessed: 1,
                preprocessedAt: '2025-03-10 04:05:06'
            });

            expect(model.partSources).to.deep.equal({
                externalReferrers: { preprocessed: true, preprocessedAt: '2025-03-10 04:05:06' }
            });
        });

        it('records a part without the flag as raw data', function () {
            var model = loadPart('externalReferrers', { referrers: [] });

            expect(model.partSources).to.deep.equal({
                externalReferrers: { preprocessed: false, preprocessedAt: '' }
            });
        });
    });
})();

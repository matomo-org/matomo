/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs');
var path = require('path');

(function () {
    describe('Live visits log "Add this segment to Matomo"', function () {

        var VisitorLog;
        var originalOpen;
        var originalUrl;

        beforeAll(function () {
            function DataTable(element) {
                this.$element = $(element);
            }
            DataTable.prototype = {
                init: function () {},
                _findReportScope: function () {
                    return $(document.body);
                },
            };

            var uiExports = { DataTable: DataTable };
            window.initializeVisitorActions = function () {};

            // The file is an IIFE taking ($, require), and the test runner's require has no 'piwik/UI'
            new Function('jQuery', 'require', fs.readFileSync(path.join(__dirname, 'visitorLog.js'), 'utf8'))(
                $,
                function () { return uiExports; }
            );

            VisitorLog = uiExports.VisitorLog;
        });

        beforeEach(function () {
            originalOpen = window.open;
            originalUrl = window.location.href;
            window.history.replaceState(null, '', '/index.php?module=CoreHome&action=index&idSite=1&period=month&date=2026-09-01#?idSite=1&period=month&date=2026-09-01&category=Goals_Ecommerce&subcategory=Ecommerce_Sales');
            document.body.innerHTML = '<a href="#" class="addSegmentToMatomo dataTableAction"></a>';
        });

        afterEach(function () {
            window.open = originalOpen;
            window.history.replaceState(null, '', originalUrl);
            document.body.innerHTML = '';
        });

        function addSegment(param) {
            var opened = [];
            window.open = function (url) { opened.push(url); };

            var visitorLog = Object.create(VisitorLog.prototype);
            visitorLog.$element = $('<div></div>');
            visitorLog.param = param;
            visitorLog.init();

            $('.addSegmentToMatomo.dataTableAction').trigger('click');

            var hash = new URLSearchParams(opened[0].split('#')[1].replace(/^\?/, ''));
            return { addSegmentAsNew: hash.get('addSegmentAsNew'), segment: hash.get('segment') };
        }

        it('adds the clicked row filter to the report segment', function () {
            var result = addSegment({
                segment: 'visitEcommerceStatus%3D%3Dordered',
                intersectSegment: 'referrerType==campaign',
            });

            expect(result.addSegmentAsNew).to.equal('visitEcommerceStatus==ordered;referrerType==campaign');
            expect(result.segment).to.equal('visitEcommerceStatus==ordered;referrerType==campaign');
        });

        it('keeps the encoded value of a row filter as it is', function () {
            var result = addSegment({
                segment: 'browserCode%3D%3DFF',
                intersectSegment: 'pageUrl==https%3A%2F%2Fexample.org%2Fblog%2F',
            });

            expect(result.addSegmentAsNew).to.equal('browserCode==FF;pageUrl==https%3A%2F%2Fexample.org%2Fblog%2F');
        });

        it('uses the report segment alone when there is no row filter', function () {
            var result = addSegment({ segment: 'browserCode%3D%3DFF' });

            expect(result.addSegmentAsNew).to.equal('browserCode==FF');
            expect(result.segment).to.equal('browserCode==FF');
        });
    });
})();

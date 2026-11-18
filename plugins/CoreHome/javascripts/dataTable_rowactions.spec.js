/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs');
var path = require('path');

(function () {
    describe('RowEvolution row action', function () {

        beforeAll(function () {
            window.eval(fs.readFileSync(path.join(__dirname, 'dataTable_rowactions.js'), 'utf8'));
        });

        /**
         * Builds a report row, or a comparison row the way _dataTableViz_htmlTable_comparisons.twig renders
         * it, with the series' segment (none for "All visits"), period and date in data-param-override.
         */
        function buildRow(paramOverride) {
            var $row = $('<table><tbody><tr data-label="Italy"><td class="label"><span class="label">Italy</span></td></tr></tbody></table>').find('tr');
            if (paramOverride) {
                $row.addClass('comparisonRow').attr('data-param-override', JSON.stringify(paramOverride));
            }
            return $row;
        }

        /** Clicks Row Evolution on the row and returns the extra params the popover requests the report with. */
        function requestedParams($row) {
            var rowAction = DataTable_RowActions_Registry.getActionByName('RowEvolution').createInstance({
                param: { module: 'UserCountry', action: 'getCountry', segment: 'browserCode==FF' },
                props: {},
            });

            var popoverLink = null;
            var openPopover = vi.spyOn(DataTable_RowAction.prototype, 'openPopover').mockImplementation(function (link) {
                popoverLink = link;
            });
            rowAction.trigger($row, new MouseEvent('click'));
            openPopover.mockRestore();

            var params = null;
            rowAction.showRowEvolution = function (apiMethod, label, extraParams) {
                params = extraParams;
            };
            rowAction.doOpenPopover(popoverLink);

            return params;
        }

        it('should request no segment for an "All visits" comparison row', function () {
            var params = requestedParams(buildRow({ period: 'month', comparePeriods: '', date: '2026-08-01', compareDates: '' }));

            expect(params.segment).to.equal('');
            expect(params.period).to.equal('month');
            expect(params.date).to.equal('2026-08-01');
        });

        it('should request the series segment for a compared segment row', function () {
            var params = requestedParams(buildRow({
                segment: 'browserCode==CH',
                compareSegments: '',
                period: 'month',
                comparePeriods: '',
                date: '2026-09-01',
                compareDates: '',
            }));

            expect(params.segment).to.equal('browserCode==CH');
            expect(params.date).to.equal('2026-09-01');
        });

        it('should leave the segment to the report on a row outside a comparison', function () {
            var params = requestedParams(buildRow(null));

            expect(params).to.not.have.property('segment');
        });
    });
})();

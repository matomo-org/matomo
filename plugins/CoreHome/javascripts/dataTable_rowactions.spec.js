/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs');
var path = require('path');

(function () {
    describe('DataTable row actions', function () {

        beforeAll(function () {
            window._pk_translate = function (key) { return key; };
            window.broadcast = { addPopoverHandler: function () {} };

            window.eval(fs.readFileSync(path.join(__dirname, 'dataTable_rowactions.js'), 'utf8'));
        });

        /**
         * Builds a searched pages report: the matching pages are shown below their expanded folder
         * rows, and no row has been hovered yet, so no row action handler is bound.
         */
        function buildSearchedTable() {
            return $(
                '<div class="dataTable dataTableActions"><table><tbody>'
                + '<tr class="level0 subDataTable"><td><span class="label">folder</span></td></tr>'
                + '<tr class="level1 subDataTable"><td><span class="label">sub</span></td></tr>'
                + '<tr class="level2"><td><span class="label">/index.html</span></td></tr>'
                + '</tbody></table></div>'
            ).appendTo(document.body).find('tr');
        }

        it('should perform the action for a row whose ancestor rows were never hovered', function () {
            var rows = buildSearchedTable();
            var action = new DataTable_RowAction(null);
            var performed = [];
            action.performAction = function (label) { performed.push(label); };

            action.trigger(rows.eq(2), {});

            expect(performed).toEqual(['folder > sub > @%2Findex.html']);
        });

        it('should perform the action for a row whose parent row was hovered', function () {
            var rows = buildSearchedTable();
            var action = new DataTable_RowAction(null);
            var performed = [];
            action.performAction = function (label) { performed.push(label); };

            action.initTr(rows.eq(0));
            action.initTr(rows.eq(1));
            action.trigger(rows.eq(2), {});

            expect(performed).toEqual(['folder > sub > @%2Findex.html']);
        });
    });
})();

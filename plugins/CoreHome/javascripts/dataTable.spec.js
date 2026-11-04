/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs');
var path = require('path');

(function () {
    describe('DataTable subtable expansion when comparing', function () {

        var DataTable;
        var requests;
        var originalAjaxHelper;
        var originalLazyScrollTo;

        beforeAll(function () {
            function UIControl(element) {
                this.$element = $(element);
            }
            UIControl.prototype = {};

            var uiExports = { UIControl: UIControl };

            // The file is an IIFE taking ($, require), and the test runner's require has no 'piwik/UI'
            new Function('jQuery', 'require', fs.readFileSync(path.join(__dirname, 'dataTable.js'), 'utf8'))(
                $,
                function () { return uiExports; }
            );

            DataTable = uiExports.DataTable;
        });

        beforeEach(function () {
            requests = [];

            originalAjaxHelper = window.ajaxHelper;
            window.ajaxHelper = function () {
                this.get = {};
                this.post = {};
                requests.push(this);
            };
            window.ajaxHelper.prototype = {
                addParams: function (params, type) {
                    Object.assign(this[type], params);
                },
                withTokenInUrl: function () {},
                setCallback: function () {},
                setErrorCallback: function () {},
                setFormat: function () {},
                send: function () {},
            };

            originalLazyScrollTo = window.piwikHelper.lazyScrollTo;
            window.piwikHelper.lazyScrollTo = function () {};

            document.body.innerHTML = '<div class="dataTable"><table class="dataTable">'
                + '<thead><tr><th>label</th><th>nb_visits</th></tr></thead>'
                + '<tbody>'
                + '<tr class="subDataTable parentComparisonRow" id="4"><td>row</td><td>5</td></tr>'
                + '<tr class="comparisonRow" data-comparison-series="0" data-idsubtable="4"><td>series 0</td><td>5</td></tr>'
                + '<tr class="comparisonRow" data-comparison-series="1" data-idsubtable="2"><td>series 1</td><td>3</td></tr>'
                + '</tbody>'
                + '</table></div>';
        });

        afterEach(function () {
            window.ajaxHelper = originalAjaxHelper;
            window.piwikHelper.lazyScrollTo = originalLazyScrollTo;
            document.body.innerHTML = '';
        });

        function expandRowOfTable(param) {
            var dataTable = Object.create(DataTable.prototype);
            dataTable.$element = $('div.dataTable');
            dataTable.workingDivId = 'dataTableWorkingDiv';
            dataTable.param = param;
            dataTable.props = { subtable_controller_action: 'getSubtable' };
            dataTable.loadedSubDataTable = {};

            dataTable.handleSubDataTable(dataTable.$element);
            $('tr.subDataTable').trigger('click');

            return dataTable;
        }

        it('requests the clicked row subtables of a table itself loaded as a subtable', function () {
            var dataTable = expandRowOfTable({
                module: 'Referrers',
                action: 'getSubtable',
                comparisonIdSubtables: '{"0":3,"1":1}',
            });

            expect(requests).to.have.length(1);
            expect(requests[0].get.idSubtable).to.equal('4');
            expect(requests[0].get).not.to.have.property('comparisonIdSubtables');
            expect(requests[0].post.comparisonIdSubtables).to.equal('{"0":4,"1":2}');

            expect(dataTable.param.comparisonIdSubtables).to.equal('{"0":3,"1":1}');
        });

        it('requests the clicked row subtables of a root table', function () {
            var dataTable = expandRowOfTable({
                module: 'Referrers',
                action: 'getSearchEngines',
            });

            expect(requests).to.have.length(1);
            expect(requests[0].get).not.to.have.property('comparisonIdSubtables');
            expect(requests[0].post.comparisonIdSubtables).to.equal('{"0":4,"1":2}');

            expect(dataTable.param).not.to.have.property('comparisonIdSubtables');
        });
    });
})();

/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs');
var path = require('path');

(function () {
    describe('JqplotPieGraphDataTable', function () {

        var exports = {};

        beforeAll(function () {
            var originalRequire = window.require;

            // minimal stand-in for the base class the pie graph extends (defined in jqplot.js)
            exports.JqplotGraphDataTable = function () {};
            exports.JqplotGraphDataTable.prototype = {
                _setJqplotParameters: function () {},
                formatY: function (value) { return String(value); }
            };

            window.NumberFormatter = { formatPercent: function (value) { return value + '%'; } };
            window.require = function () { return exports; };

            try {
                window.eval(fs.readFileSync(path.join(__dirname, 'jqplotPieGraph.js'), 'utf8'));
            } finally {
                window.require = originalRequire;
            }
        });

        afterEach(function () {
            vi.restoreAllMocks();
        });

        /**
         * Shows the data point tooltip for a slice whose label is the (decoded) x-axis tick and
         * returns the HTML content handed to the jQuery UI tooltip.
         */
        function tooltipContentForLabel(label) {
            var graph = Object.create(exports.JqplotPieGraphDataTable.prototype);
            graph.jqplotParams = { series: [{ label: 'Visits' }] };
            graph.data = [[[label, 5]]];
            graph.tooltip = { percentages: [[100]] };

            var options;
            vi.spyOn($.fn, 'tooltip').mockImplementation(function (opts) {
                options = opts;
                return this;
            });

            graph._showDataPointTooltip($('<div>').get(0), 0, 0);

            return options.content;
        }

        it('escapes the slice label in the tooltip header', function () {
            var label = 'Tom & Jerry <img src=x onerror=alert(1)> "quoted"';
            var $content = $('<div>').html(tooltipContentForLabel(label));

            expect($content.find('img').length).to.equal(0);
            expect($content.find('h3').text()).to.equal(label);
        });

        it('shows numeric slice labels', function () {
            var $content = $('<div>').html(tooltipContentForLabel(2024));

            expect($content.find('h3').text()).to.equal('2024');
        });
    });
})();

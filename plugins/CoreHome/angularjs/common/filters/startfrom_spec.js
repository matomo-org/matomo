/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    describe('startFromFilter', function() {
        var startFrom;

        beforeEach(module('piwikApp.filter'));
        beforeEach(inject(function($injector) {
            var $filter = $injector.get('$filter');
            startFrom = $filter('startFrom');
        }));

        describe('#startFrom()', function() {

            it('should return all entries if index is zero', function() {

                var result = startFrom([1,2,3], 0);

                expect(result).to.eql([1,2,3]);
            });

            it('should return only partial entries if filter is higher than zero', function() {

                var result = startFrom([1,2,3], 2);

                expect(result).to.eql([3]);
            });

            it('should return no entries if start is higher than input length', function() {

                var result = startFrom([1,2,3], 11);

                expect(result).to.eql([]);
            });
        });
    });
})();
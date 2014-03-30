/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('startFrom Filter Test', function() {
    var $filter;

    beforeEach(function() {
        module('piwikApp.filter');
        inject(function($injector) {
            $filter = $injector.get('$filter');
        });
    });

    it('should fetch all websites', function() {
        var startFrom = $filter('startFrom');

        var result = startFrom([1,2,3], 2);

        expect(result).to.eql([3]);
    });
});
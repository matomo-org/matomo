/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    describe('broadcast', function () {

        var tests = [
            { url: 'http://matomo.org/index.php?param=val', param: 'param', expectedValue: 'val' },
            { url: 'http://matomo.org/index.php?param=val', param: 'custom', expectedValue: '' },
            { url: 'http://matomo.org/index.php?myparam=test&param=val', param: 'param', expectedValue: 'val' },
            { url: 'http://matomo.org/index.php?param=val&myparam=test', param: 'param', expectedValue: 'val' },
            { url: 'http://matomo.org/index.php?param=val&param=val2', param: 'param', expectedValue: 'val2' },
            { url: 'http://matomo.org/index.php?param[]=val&myparam[]=x&param[]=val2', param: 'param', expectedValue: ['val', 'val2'] },
        ];

        tests.forEach(function(test,index) {
            it('should return correct parameters with getParamValue ' + (index+1), function () {
                expect(broadcast.getParamValue(test.param, test.url)).to.deep.equal(test.expectedValue);
            });
        });
    });
})();

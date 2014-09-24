/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    describe('piwikService', function() {
        var piwikService;

        beforeEach(module('piwikApp.service'));
        beforeEach(inject(function($injector) {
            piwikService = $injector.get('piwik');
        }));

        describe('#piwikService', function() {

            it('should be the same as piwik global var', function() {
                piwik.should.equal(piwikService);
            });

            it('should mixin broadcast', function() {
                expect(piwikService.broadcast).to.be.an('object');
            });

            it('should mixin piwikHelper', function() {
                expect(piwikService.helper).to.be.an('object');
            });
        });

        describe('#piwik_url', function() {

            it('should contain the piwik url', function() {
                expect(piwikService.piwik_url).to.eql('http://localhost/');
            });
        });
    });
})();
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    describe('piwikHelper', function() {
        var piwikHelper;

        beforeEach(module('piwikApp.service'));
        beforeEach(inject(function ($injector) {
            piwikHelper = $injector.get('piwik').helper;
        }));
        beforeEach(function () {
            delete window._dosomething;
        });

        describe('#htmlDecode', function () {

            it('should correctly decode html entities', function (done) {
                var called = false;
                window._dosomething = function () {
                    called = true;
                };

                var encoded = 'str <img src=\'x/\' onerror=\'_dosomething()\'/>';
                var decoded = piwikHelper.htmlDecode(encoded);

                setTimeout(function () {
                    expect(called).to.be.false;
                    expect(decoded).to.equal('str <img src=\'x/\' onerror=\'_dosomething()\'/>');
                    done();
                }, 500);
            });

        });
    });

})();
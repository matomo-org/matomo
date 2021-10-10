/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import '../../../../Morpheus/javascripts/piwikHelper';

describe('CoreHome/piwikHelper', () => {
  describe('#htmlDecode', () => {

    it('should correctly decode html entities', function (done) {
      let called = false;
      (window as any)._testfunction = () => {
        called = true;
      };

      const encoded = 'str <img src=\'x/\' onerror=\'_testfunction()\'/>';
      const decoded = window.piwikHelper.htmlDecode(encoded);

      setTimeout(() => {
        expect(called).toBe(false);
        expect(decoded).toEqual('str <img src=\'x/\' onerror=\'_testfunction()\'/>');
        done();
      }, 500);
    });
  });
});

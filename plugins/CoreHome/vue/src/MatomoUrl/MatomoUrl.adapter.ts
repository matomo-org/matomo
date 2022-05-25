/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import MatomoUrl from './MatomoUrl';

function piwikUrl() {
  const model = {
    getSearchParam: MatomoUrl.getSearchParam.bind(MatomoUrl),
  };

  return model;
}

window.angular.module('piwikApp.service').service('piwikUrl', piwikUrl);

// make sure $location is initialized early
window.angular.module('piwikApp.service').run(['$location', () => null]);

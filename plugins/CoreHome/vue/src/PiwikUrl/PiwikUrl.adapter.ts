/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import PiwikUrl from './PiwikUrl';

function piwikUrl() {
  const model = {
    getSearchParam: PiwikUrl.getSearchParam.bind(PiwikUrl),
  };

  return model;
}

piwikUrl.$inject = [];

angular.module('piwikApp.service').service('piwikUrl', piwikUrl);

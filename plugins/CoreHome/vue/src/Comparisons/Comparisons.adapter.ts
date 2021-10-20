/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import ComparisonsStore from './Comparisons.store';
import Comparisons from './Comparisons.vue';

function ComparisonFactory() {
  return ComparisonsStore;
}

ComparisonFactory.$inject = [];

angular.module('piwikApp.service').factory('piwikComparisonsService', ComparisonFactory);

export default createAngularJsAdapter({
  component: Comparisons,
  directiveName: 'piwikComparisons',
});

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import SitesManagement from './SitesManagement.vue';

export default createAngularJsAdapter({
  component: SitesManagement,
  scope: {},
  directiveName: 'matomoSitesManagement',
});

// sitesManagerAPI no longer exists, but it is still referenced by a premium feature. the feature
// doesn't actually use it though so we can just create an empty object for an adapter.
window.angular.module('piwikApp').factory('sitesManagerAPI', () => ({}));

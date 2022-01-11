/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import SitesStore from './SitesStore';
import { cloneThenApply } from '../createAngularJsAdapter';

function siteSelectorModelAdapter() {
  return {
    get initialSites() {
      return SitesStore.initialSites.value;
    },
    loadSite: SitesStore.loadSite.bind(SitesStore),
    loadInitialSites: () => cloneThenApply(SitesStore.loadInitialSites()),
    searchSite: (...args: Parameters<typeof SitesStore['searchSite']>) => cloneThenApply(
      SitesStore.searchSite(...args),
    ),
  };
}

window.angular.module('piwikApp.service').factory('siteSelectorModel', siteSelectorModelAdapter);

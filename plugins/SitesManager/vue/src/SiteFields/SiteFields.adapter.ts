/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter, Site, MatomoUrl } from 'CoreHome';
import SiteFields from './SiteFields.vue';

export default createAngularJsAdapter({
  component: SiteFields,
  scope: {
    site: {
      angularJsBind: '<',
    },
    timezoneSupportEnabled: {
      angularJsBind: '<',
    },
    utcTime: {
      angularJsBind: '<',
    },
    globalSettings: {
      angularJsBind: '<',
    },
  },
  directiveName: 'matomoSiteFields',
  events: {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    delete(site: Site, vm, scope: any) {
      let redirectParams = scope.redirectParams;

      // if the current idSite in the URL is the site we're deleting, then we have to make to change it. otherwise,
      // if a user goes to another page, the invalid idSite may cause a fatal error.
      if (MatomoUrl.urlParsed.value.idSite == site.idsite) {
        const sites = scope.adminSites.sites as Site[];
        const otherSite = sites.find((s) => s.idsite !== site.idsite);

        if (otherSite) {
          redirectParams = { ...redirectParams, idSite: otherSite.idsite };
        }
      }

      MatomoUrl.updateUrl(
        {
          ...MatomoUrl.urlParsed.value,
          redirectParams,
        },
        MatomoUrl.hashParsed.value,
      );
    },
  },
});

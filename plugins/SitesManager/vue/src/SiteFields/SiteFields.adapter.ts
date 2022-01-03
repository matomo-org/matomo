/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { ITimeoutService } from 'angular';
import { createAngularJsAdapter, Site, MatomoUrl } from 'CoreHome';
import { Setting } from 'CorePluginsAdmin';
import SiteFields from './SiteFields.vue';

export default createAngularJsAdapter<[ITimeoutService]>({
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
  $inject: ['$timeout'],
  events: {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    delete(site: Site, vm, scope: any) {
      let { redirectParams } = scope;

      // if the current idSite in the URL is the site we're deleting, then we have to make to
      // change it. otherwise, if a user goes to another page, the invalid idSite may cause
      // a fatal error.
      if (MatomoUrl.urlParsed.value.idSite === site.idsite) {
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
    save(
      { site, settingValues }: { site: Site, settingValues: Record<string, Setting[]> },
      vm,
      scope: any,
      element,
      attrs,
      controller,
      $timeout,
    ) {
      const texttareaArrayParams = ['excluded_ips', 'excluded_parameters', 'excluded_user_agents'];

      const newSite: Site = { ...site };
      Object.values(settingValues).forEach((settings) => {
        settings.forEach((setting) => {
          if (setting.name === 'urls') {
            newSite.alias_urls = setting.value;
          } else if (texttareaArrayParams.indexOf(setting.name) !== -1) {
            newSite[setting.name] = setting.value.join(', ');
          } else {
            newSite[setting.name] = setting.value;
          }
        });
      });

      window.$.extend(scope.site, newSite);
      $timeout();

      vm.site = newSite;
    },
  },
});

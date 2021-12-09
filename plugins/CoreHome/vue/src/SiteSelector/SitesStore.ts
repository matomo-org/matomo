/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive, computed, readonly } from 'vue';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import MatomoUrl from '../MatomoUrl/MatomoUrl';

export interface Site {
  idsite: string;
  name: string;
}

interface SitesStoreState {
  initialSites: Site[]|null;
  isInitialized: boolean;
}

class SitesStore {
  private state = reactive<SitesStoreState>({
    initialSites: [],
    isInitialized: false,
  });

  private currentRequest: AbortablePromise;

  private limitRequest: AbortablePromise;

  public readonly initialSites = computed(() => readonly(this.state.initialSites));

  loadInitialSites(): Promise<Site[]> {
    if (this.state.isInitialized) {
      return Promise.resolve(readonly(this.state.initialSites));
    }

    return this.searchSite('%').then((sites) => {
      this.state.isInitialized = true;
      this.state.initialSites = sites;
      return readonly(sites);
    });
  }

  loadSite(idSite: number|string): void {
    if (idSite === 'all') {
      MatomoUrl.updateUrl({
        ...MatomoUrl.urlParsed.value,
        module: 'MultiSites',
        action: 'index',
        date: MatomoUrl.parsed.value.date,
        period: MatomoUrl.parsed.value.period,
      });
    } else {
      MatomoUrl.updateUrl({
        ...MatomoUrl.urlParsed.value,
        segment: '',
        idSite,
      }, MatomoUrl.hashParsed.value);
    }
  }

  searchSite(term, onlySitesWithAdminAccess = false): Promise<Site[]> {
    if (!term) {
      return this.loadInitialSites();
    }

    if (this.currentRequest) {
      this.currentRequest.abort();
    }

    if (!this.limitRequest) {
      this.limitRequest = AjaxHelper.fetch({ method: 'SitesManager.getNumWebsitesToDisplayPerPage' });
    }

    return this.limitRequest.then((response) => {
      const limit = response.value;

      let methodToCall = 'SitesManager.getPatternMatchSites';
      if (onlySitesWithAdminAccess) {
        methodToCall = 'SitesManager.getSitesWithAdminAccess';
      }

      this.currentRequest = AjaxHelper.fetch({
        method: methodToCall,
        limit,
        pattern: term,
      });

      return this.currentRequest;
    }).then((response) => {
      if (response) {
        return this.processWebsitesList(response);
      }

      return null;
    }).finally(() => {
      this.currentRequest = null;
    });
  }

  private processWebsitesList(response) {
    let sites = response;

    if (!sites || !sites.length) {
      return [];
    }

    sites = sites.map((s) => ({
      ...s,
      name: s.group ? `[${s.group}] ${s.name}` : s.name,
    }));

    sites.sort((lhs, rhs) => {
      if (lhs.name.toLowerCase() < rhs.name.toLowerCase()) {
        return -1;
      }
      return lhs.name.toLowerCase() > rhs.name.toLowerCase() ? 1 : 0;
    });

    return sites;
  }
}

export default new SitesStore();

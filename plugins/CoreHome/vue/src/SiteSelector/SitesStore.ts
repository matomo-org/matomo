/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  reactive,
  computed,
  readonly,
  DeepReadonly,
} from 'vue';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import Site from './Site';

interface SitesStoreState {
  initialSites: DeepReadonly<Site[]>;
  initialSitesFiltered: DeepReadonly<Site[]>;
  isInitialized: boolean;
}

class SitesStore {
  private state = reactive<SitesStoreState>({
    initialSites: [],
    initialSitesFiltered: [],
    isInitialized: false,
  });

  private currentRequestAbort: AbortController | null = null;

  private limitRequest?: Promise<{ value: number|string }>;

  private sitesToExclude: number[] = [];

  public readonly initialSites = computed(() => readonly(this.state.initialSites));

  public readonly initialSitesFiltered = computed(() => readonly(this.state.initialSitesFiltered));

  loadInitialSites(onlySitesWithAdminAccess = false,
    returnFilteredSites: null|boolean = false): Promise<DeepReadonly<Site[]>|null> {
    if (this.state.isInitialized) {
      return Promise.resolve(readonly(returnFilteredSites
        ? this.state.initialSitesFiltered : this.state.initialSites));
    }

    return this.searchSite('%', onlySitesWithAdminAccess, returnFilteredSites).then((sites) => {
      this.state.isInitialized = true;
      if (sites !== null) {
        this.state.initialSites = sites;
        this.state.initialSitesFiltered = readonly(sites);
      }
      return sites;
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
      }, {
        ...MatomoUrl.hashParsed.value,
        segment: '',
        idSite,
      });
    }
  }

  searchSite(term?: string, onlySitesWithAdminAccess = false,
    returnFilteredSites: null|boolean = false): Promise<DeepReadonly<Site[]>|null> {
    if (!term) {
      return this.loadInitialSites(onlySitesWithAdminAccess, returnFilteredSites);
    }

    if (this.currentRequestAbort) {
      this.currentRequestAbort.abort();
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

      this.currentRequestAbort = new AbortController();
      return AjaxHelper.fetch({
        method: methodToCall,
        limit,
        pattern: term,
      }, {
        abortController: this.currentRequestAbort,
      });
    }).then((response) => {
      if (response) {
        return this.processWebsitesList(response as Site[], returnFilteredSites);
      }

      return null;
    }).finally(() => {
      this.currentRequestAbort = null;
    });
  }

  setSitesToExclude(sitesToExclude: number[]) {
    this.sitesToExclude = sitesToExclude;
    this.state.isInitialized = false; // Set this so that things get re-initialized.
  }

  private processWebsitesList(response: Site[], returnFilteredSites: null|boolean = false): Site[] {
    let sites = response;

    if (!sites || !sites.length) {
      return [];
    }

    sites = sites.map((s) => ({
      ...s,
      name: s.group ? `[${s.group}] ${s.name}` : s.name,
    }));

    sites.sort((lhs: Site, rhs: Site) => {
      if (lhs.name.toLowerCase() < rhs.name.toLowerCase()) {
        return -1;
      }
      return lhs.name.toLowerCase() > rhs.name.toLowerCase() ? 1 : 0;
    });

    if (!this.sitesToExclude || this.sitesToExclude.length === 0) {
      return sites;
    }

    const filteredSites: Site[] = [];
    sites.forEach((site) => {
      const idSite = typeof site.idsite === 'string' ? parseInt(site.idsite, 10) : site.idsite;
      if (!this.sitesToExclude.includes(idSite)) {
        filteredSites.push(site);
      }
    });

    return returnFilteredSites ? filteredSites : sites;
  }
}

export default new SitesStore();

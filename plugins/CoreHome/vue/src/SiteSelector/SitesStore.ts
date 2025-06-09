/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
  isInitialized: boolean;
}

interface SitesStoreStateFiltered extends SitesStoreState {
  excludedSites: number[];
}

class SitesStore {
  private state = reactive<SitesStoreState>({
    initialSites: [],
    isInitialized: false,
  });

  private stateFiltered = reactive<SitesStoreStateFiltered>({
    initialSites: [],
    isInitialized: false,
    excludedSites: [],
  });

  private currentRequestAbort: AbortController | null = null;

  private limitRequest?: Promise<{ value: number|string }>;

  public readonly initialSites = computed(() => readonly(this.state.initialSites));

  public readonly initialSitesFiltered = computed(() => readonly(this.stateFiltered.initialSites));

  loadInitialSites(onlySitesWithAdminAccess = false,
    sitesToExclude: number[] = []): Promise<DeepReadonly<Site[]>|null> {
    if (this.state.isInitialized && sitesToExclude.length === 0) {
      return Promise.resolve(readonly(this.state.initialSites));
    }

    // If the filtered state has already been initialized with the same sites, return that.
    if (this.stateFiltered.isInitialized
      && sitesToExclude.length === this.stateFiltered.excludedSites.length
      && (sitesToExclude.every((val, index) => val === this.stateFiltered.excludedSites[index]))) {
      return Promise.resolve(readonly(this.stateFiltered.initialSites));
    }

    // If we want to exclude certain sites, perform the search for that.
    if (sitesToExclude.length > 0) {
      this.searchSite('%', onlySitesWithAdminAccess, sitesToExclude).then((sites) => {
        this.stateFiltered.isInitialized = true;
        this.stateFiltered.excludedSites = sitesToExclude;
        if (sites !== null) {
          this.stateFiltered.initialSites = sites;
        }
      });
    }

    // If the main state has already been initialized, no need to continue.
    if (this.state.isInitialized) {
      return Promise.resolve(readonly(this.state.initialSites));
    }

    return this.searchSite('%', onlySitesWithAdminAccess, sitesToExclude).then((sites) => {
      this.state.isInitialized = true;
      if (sites !== null) {
        this.state.initialSites = sites;
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
    sitesToExclude: number[] = []): Promise<DeepReadonly<Site[]>|null> {
    if (!term) {
      return this.loadInitialSites(onlySitesWithAdminAccess, sitesToExclude);
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
        sitesToExclude,
      }, {
        abortController: this.currentRequestAbort,
        abortable: false,
      });
    }).then((response) => {
      if (response) {
        return this.processWebsitesList(response as Site[]);
      }

      return null;
    }).finally(() => {
      this.currentRequestAbort = null;
    });
  }

  private processWebsitesList(response: Site[]): Site[] {
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

    return sites;
  }
}

export default new SitesStore();

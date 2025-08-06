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
  onlySitesWithAdminAccess: boolean;
  onlySitesWithAtLeastWriteAccess: boolean;
  siteTypesToExclude: string[];
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
    onlySitesWithAdminAccess: false,
    onlySitesWithAtLeastWriteAccess: false,
    siteTypesToExclude: [],
  });

  private currentRequestAbort: AbortController | null = null;

  private limitRequest?: Promise<{ value: number|string }>;

  public readonly initialSites = computed(() => readonly(this.state.initialSites));

  public readonly initialSitesFiltered = computed(() => readonly(this.stateFiltered.initialSites));

  isFiltered(
    onlySitesWithAdminAccess = false,
    sitesToExclude: number[] = [],
    onlySitesWithAtLeastWriteAccess = false,
    siteTypesToExclude: string[] = [],
  ): boolean {
    return sitesToExclude.length > 0
      || onlySitesWithAdminAccess
      || onlySitesWithAtLeastWriteAccess
      || siteTypesToExclude.length > 0;
  }

  matchesCurrentFilteredState(
    onlySitesWithAdminAccess = false,
    sitesToExclude: number[] = [],
    onlySitesWithAtLeastWriteAccess = false,
    siteTypesToExclude: string[] = [],
  ): boolean {
    // If the filtered state hasn't been initialised yet and no filters are applied, return true
    if (!this.stateFiltered.isInitialized && !this.isFiltered(
      onlySitesWithAdminAccess, sitesToExclude, onlySitesWithAtLeastWriteAccess, siteTypesToExclude,
    )) {
      return true;
    }

    // Run deep comparison to ensure the filters are actually the same
    return this.stateFiltered.isInitialized
      && sitesToExclude.length === this.stateFiltered.excludedSites.length
      && (sitesToExclude.every((val, index) => val === this.stateFiltered.excludedSites[index]))
      && onlySitesWithAdminAccess === this.stateFiltered.onlySitesWithAdminAccess
      && onlySitesWithAtLeastWriteAccess === this.stateFiltered.onlySitesWithAtLeastWriteAccess
      && siteTypesToExclude.length === this.stateFiltered.siteTypesToExclude.length
      && (
        siteTypesToExclude.every(
          (val, index) => val === this.stateFiltered.siteTypesToExclude[index],
        )
      );
  }

  loadInitialSites(
    onlySitesWithAdminAccess = false,
    sitesToExclude: number[] = [],
    onlySitesWithAtLeastWriteAccess = false,
    siteTypesToExclude: string[] = [],
  ): Promise<DeepReadonly<Site[]>|null> {
    if (this.state.isInitialized && !this.isFiltered(
      onlySitesWithAdminAccess, sitesToExclude, onlySitesWithAtLeastWriteAccess, siteTypesToExclude,
    )) {
      return Promise.resolve(readonly(this.state.initialSites));
    }

    // If the filtered state has already been initialized with the same sites, return that.
    if (this.stateFiltered.isInitialized && this.matchesCurrentFilteredState(
      onlySitesWithAdminAccess, sitesToExclude, onlySitesWithAtLeastWriteAccess, siteTypesToExclude,
    )) {
      return Promise.resolve(readonly(this.stateFiltered.initialSites));
    }

    // If we want to exclude certain sites, perform the search for that.
    if (this.isFiltered(
      onlySitesWithAdminAccess, sitesToExclude, onlySitesWithAtLeastWriteAccess, siteTypesToExclude,
    )) {
      return this.searchSite(
        '%',
        onlySitesWithAdminAccess,
        sitesToExclude,
        onlySitesWithAtLeastWriteAccess,
        siteTypesToExclude,
      ).then((sites) => {
        this.stateFiltered.isInitialized = true;
        this.stateFiltered.excludedSites = sitesToExclude;
        this.stateFiltered.onlySitesWithAdminAccess = onlySitesWithAdminAccess;
        this.stateFiltered.onlySitesWithAtLeastWriteAccess = onlySitesWithAtLeastWriteAccess;
        this.stateFiltered.siteTypesToExclude = siteTypesToExclude;
        if (sites !== null) {
          this.stateFiltered.initialSites = sites;
        }
        return sites;
      });
    }

    // If the main state has already been initialized, no need to continue.
    if (this.state.isInitialized) {
      return Promise.resolve(readonly(this.state.initialSites));
    }

    return this.searchSite(
      '%',
      onlySitesWithAdminAccess,
      sitesToExclude,
      onlySitesWithAtLeastWriteAccess,
      siteTypesToExclude,
    ).then((sites) => {
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

  searchSite(
    term?: string,
    onlySitesWithAdminAccess = false,
    sitesToExclude: number[] = [],
    onlySitesWithAtLeastWriteAccess = false,
    siteTypesToExclude: string[] = [],
  ): Promise<DeepReadonly<Site[]>|null> {
    if (!term) {
      return this.loadInitialSites(
        onlySitesWithAdminAccess,
        sitesToExclude,
        onlySitesWithAtLeastWriteAccess,
        siteTypesToExclude,
      );
    }

    if (this.currentRequestAbort) {
      this.currentRequestAbort.abort();
    }

    if (!this.limitRequest) {
      this.limitRequest = AjaxHelper.fetch({ method: 'SitesManager.getNumWebsitesToDisplayPerPage' });
    }

    return this.limitRequest.then((response) => {
      const limit = response.value;

      let permission = 'view';
      if (onlySitesWithAdminAccess) {
        permission = 'admin';
      } else if (onlySitesWithAtLeastWriteAccess) {
        permission = 'write';
      }

      this.currentRequestAbort = new AbortController();
      return AjaxHelper.fetch({
        method: 'SitesManager.getSitesWithMinimumAccess',
        permission,
        limit,
        pattern: term,
        sitesToExclude,
        siteTypesToExclude,
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

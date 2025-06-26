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
  sitesWithAtLeastWriteAccess: boolean;
  excludeSiteTypes: string[];
  excludedSitesByType: number[];
  retrySearchCount: number;
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
    sitesWithAtLeastWriteAccess: false,
    excludeSiteTypes: [],
    excludedSitesByType: [],
    retrySearchCount: 0,
  });

  private currentRequestAbort: AbortController | null = null;

  private limitRequest?: Promise<{ value: number|string }>;

  public readonly initialSites = computed(() => readonly(this.state.initialSites));

  public readonly initialSitesFiltered = computed(() => readonly(this.stateFiltered.initialSites));

  loadInitialSites(
    onlySitesWithAdminAccess = false,
    sitesToExclude: number[] = [],
    sitesWithAtLeastWriteAccess = false,
    siteTypesToExclude: string[] = [],
  ): Promise<DeepReadonly<Site[]>|null> {
    // If the types of sites to exclude list is different, clear it and the related state values
    if (
      siteTypesToExclude.length !== this.stateFiltered.excludeSiteTypes.length
      || (
        !siteTypesToExclude.every(
          (val, index) => val === this.stateFiltered.excludeSiteTypes[index],
        )
      )
    ) {
      this.stateFiltered.excludeSiteTypes = [];
      this.stateFiltered.excludedSitesByType = [];
    }

    if (
      this.state.isInitialized
      && sitesToExclude.length === 0
      && onlySitesWithAdminAccess === false
      && sitesWithAtLeastWriteAccess === false
      && siteTypesToExclude.length === 0
    ) {
      return Promise.resolve(readonly(this.state.initialSites));
    }

    // If the filtered state has already been initialized with the same sites, return that.
    if (this.stateFiltered.isInitialized
      && sitesToExclude.length === this.stateFiltered.excludedSites.length
      && (sitesToExclude.every((val, index) => val === this.stateFiltered.excludedSites[index]))
      && onlySitesWithAdminAccess === this.stateFiltered.onlySitesWithAdminAccess
      && sitesWithAtLeastWriteAccess === this.stateFiltered.sitesWithAtLeastWriteAccess
      && siteTypesToExclude.length === this.stateFiltered.excludeSiteTypes.length
      && (
        siteTypesToExclude.every((val, index) => val === this.stateFiltered.excludeSiteTypes[index])
      )
    ) {
      return Promise.resolve(readonly(this.stateFiltered.initialSites));
    }

    // If we want to exclude certain sites, perform the search for that.
    let isFilteredSearch = false;
    if (
      sitesToExclude.length > 0
      || onlySitesWithAdminAccess
      || sitesWithAtLeastWriteAccess
      || siteTypesToExclude.length > 0
    ) {
      isFilteredSearch = true;
      const searchPromise = this.searchSite(
        '%',
        onlySitesWithAdminAccess,
        sitesToExclude,
        sitesWithAtLeastWriteAccess,
        siteTypesToExclude,
      ).then((sites) => {
        this.stateFiltered.isInitialized = true;
        this.stateFiltered.excludedSites = sitesToExclude;
        if (sites !== null) {
          this.stateFiltered.initialSites = sites;
        }
        this.stateFiltered.onlySitesWithAdminAccess = onlySitesWithAdminAccess;
        this.stateFiltered.sitesWithAtLeastWriteAccess = sitesWithAtLeastWriteAccess;
        this.stateFiltered.excludeSiteTypes = siteTypesToExclude;

        return sites;
      });

      // Don't bother with the rest if the state has already been initialised
      if (this.state.isInitialized === true) {
        return searchPromise;
      }
    }

    // If the main state has already been initialized, no need to continue.
    if (!isFilteredSearch && this.state.isInitialized) {
      return Promise.resolve(readonly(this.state.initialSites));
    }

    return this.searchSite(
      '%',
      onlySitesWithAdminAccess,
      sitesToExclude,
      sitesWithAtLeastWriteAccess,
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
    sitesWithAtLeastWriteAccess = false,
    siteTypesToExclude: string[] = [],
  ): Promise<DeepReadonly<Site[]>|null> {
    if (!term) {
      return this.loadInitialSites(
        onlySitesWithAdminAccess,
        sitesToExclude,
        sitesWithAtLeastWriteAccess,
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

      let methodToCall = 'SitesManager.getPatternMatchSites';
      // onlySitesWithAdminAccess is given precedence because it's more restrictive
      // Combining these two would have been preferable, but trying to preserve compatibility
      if (onlySitesWithAdminAccess) {
        methodToCall = 'SitesManager.getSitesWithAdminAccess';
      } else if (sitesWithAtLeastWriteAccess) {
        methodToCall = 'SitesManager.getSitesWithAtLeastWriteAccess';
      }

      // Recursively search until all sites of excluded types, if any, are excluded
      this.currentRequestAbort = new AbortController();
      return AjaxHelper.fetch({
        method: methodToCall,
        limit,
        pattern: term,
        // Exclude the provided sites and those identified as types to be excluded
        sitesToExclude: [
          ...sitesToExclude,
          ...this.stateFiltered.excludedSitesByType,
        ],
      }, {
        abortController: this.currentRequestAbort,
        abortable: false,
      });
    }).then((response) => {
      if (response) {
        const tempExclusionListCount = this.stateFiltered.excludedSitesByType.length;
        const result = this.processWebsitesList(response as Site[], siteTypesToExclude);

        // If there were additional sites excluded, run the search again until no more are added
        if (
          tempExclusionListCount !== this.stateFiltered.excludedSitesByType.length
        ) {
          if (this.stateFiltered.retrySearchCount >= 10) {
            this.stateFiltered.retrySearchCount = 0;
            throw new Error('Retry count of 10 exceeded when trying to exclude all sites based on type');
          }
          console.log('Running the search again as some of the results were an excluded site type');
          this.stateFiltered.retrySearchCount += 1;
          // Clear the currentRequestAbort before making a recursive call as the request is done
          this.currentRequestAbort = null;
          return this.searchSite(
            term,
            onlySitesWithAdminAccess,
            sitesToExclude,
            sitesWithAtLeastWriteAccess,
            siteTypesToExclude,
          );
        }

        // Since the result is complete, clear the retry count
        this.stateFiltered.retrySearchCount = 0;
        return result;
      }

      return null;
    }).finally(() => {
      this.currentRequestAbort = null;
    });
  }

  private processWebsitesList(response: Site[], siteTypesToExclude: string[] = []): Site[] {
    let sites = response;

    if (!sites || !sites.length) {
      return [];
    }

    // Make sure that all exclusion type entries are lowercase for easier comparison
    const excludeSiteTypes = siteTypesToExclude.map((str) => str.toLowerCase());

    // Add group to site name and filter out roll-ups if flag is set
    sites = sites.reduce(
      (tempSites: Site[], s: Site) => {
        // If types are excluded, identify sites of that type and exclude them from future searches
        if (excludeSiteTypes.length > 0 && excludeSiteTypes.includes(s.type.toLowerCase().trim())) {
          this.stateFiltered.excludedSitesByType.push(s.idsite as number);
        } else {
          tempSites.push({
            ...s,
            name: s.group ? `[${s.group}] ${s.name}` : s.name,
          });
        }

        return tempSites;
      },
      [],
    );

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

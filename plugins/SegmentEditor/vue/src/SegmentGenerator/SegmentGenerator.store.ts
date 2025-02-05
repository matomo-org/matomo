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
import { AjaxHelper } from 'CoreHome';
import { SegmentMetadata } from '../types';

interface SiteSettings {
  siteId: number,
  settings: any
}

interface SegmentGeneratorStoreState {
  isLoading: boolean;
  segments: SegmentMetadata[];
  sitesSettings: SiteSettings[];
}

class SegmentGeneratorStore {
  private privateState: SegmentGeneratorStoreState = reactive<SegmentGeneratorStoreState>({
    isLoading: false,
    segments: [],
    sitesSettings: [],
  });

  readonly state = computed(() => readonly(this.privateState));

  private loadSegmentsAbort?: AbortController;
  private loadSitesSettingsAbort?: AbortController;

  private loadSegmentsPromise?: Promise<SegmentMetadata[]>;
  private loadSitesSettingsPromise?: Promise<SiteSettings[]>;

  private fetchedSiteId?: string|number;

  loadSiteSettings(siteId: string|number) {
    if (typeof siteId !== 'number') {
      console.log("All = bad");
      return Promise.resolve([]);
    }

    return AjaxHelper.fetch<SiteSettings[]>({
      method: 'SitesManager.getSiteSettings',
      siteId
    });
  }

  checkIfVisitorLogOrProfileDisabled(siteId: string|number) : Promise<boolean> {
    return Promise.all([
      AjaxHelper.fetch<boolean>({
        method: 'Live.isVisitorLogEnabled',
        idSite: siteId
      }),
      AjaxHelper.fetch<boolean>({
        method: 'Live.isVisitorProfileEnabled',
        idSite: siteId
      })]).then(([res1,res2]) => {
        return !res1.value || !res2.value;
      });
  }

  loadSitesSettings(
    siteId?: string|number
  ): Promise<DeepReadonly<SiteSettings>> {

    if (this.loadSitesSettingsAbort) {
      this.loadSitesSettingsAbort.abort();
      this.loadSitesSettingsAbort = undefined;
    }

    this.privateState.isLoading = true;

    if (this.fetchedSiteId !== siteId) {
      this.loadSitesSettingsAbort = undefined;
      this.fetchedSiteId = siteId;
    }

    if (!this.loadSitesSettingsPromise) {
      let idSites: string|number|undefined = undefined;
      let idSite: string|number|undefined = undefined;

      if (siteId === 'all' || !siteId) {
        //idSites ='all';
        //idSite = 'all';
        this.loadSitesSettingsPromise = Promise.resolve([]);
      } else if (siteId) {
        idSites = siteId;
        idSite = siteId;
        this.loadSitesSettingsAbort = new AbortController();
        this.loadSitesSettingsPromise = AjaxHelper.fetch<SiteSettings[]>({
          method: 'SitesManager.getSiteSettings',
          idSite
        });
      }

    }

    return this.loadSitesSettingsPromise.then((response: SiteSettings[]) => {
      this.privateState.isLoading = false;

      if (response) {
        this.privateState.sitesSettings = response;
      }

      return this.state.value.sitesSettings;
    }).finally(() => {
      this.privateState.isLoading = false;
      delete this.loadSitesSettingsPromise;
    })
  }


  loadSegments(
    siteId?: string|number,
    visitSegmentsOnly?: boolean,
  ): Promise<DeepReadonly<SegmentMetadata[]>> {
    if (this.loadSegmentsAbort) {
      this.loadSegmentsAbort.abort();
      this.loadSegmentsAbort = undefined;
    }

    this.privateState.isLoading = true;

    if (this.fetchedSiteId !== siteId) {
      this.loadSegmentsAbort = undefined;
      this.fetchedSiteId = siteId;
    }

    if (!this.loadSegmentsPromise) {
      let idSites: string|number|undefined = undefined;
      let idSite: string|number|undefined = undefined;

      if (siteId === 'all' || !siteId) {
        idSites = 'all';
        idSite = 'all';
      } else if (siteId) {
        idSites = siteId;
        idSite = siteId;
      }

      this.loadSegmentsAbort = new AbortController();
      this.loadSegmentsPromise = AjaxHelper.fetch<SegmentMetadata[]>({
        method: 'API.getSegmentsMetadata',
        filter_limit: '-1',
        _hideImplementationData: 0,
        idSites,
        idSite,
      });
    }

    return this.loadSegmentsPromise.then((response) => {
      this.privateState.isLoading = false;

      if (response) {
        if (visitSegmentsOnly) {
          this.privateState.segments = response.filter(
            (s) => s.sqlSegment && s.sqlSegment.match(/log_visit\./),
          );
        } else {
          this.privateState.segments = response;
        }
      }

      return this.state.value.segments;
    }).finally(() => {
      this.privateState.isLoading = false;
      delete this.loadSegmentsPromise;
    });
  }
}

export default new SegmentGeneratorStore();

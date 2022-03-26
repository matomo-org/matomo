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
import { AjaxHelper } from 'CoreHome';
import { SegmentMetadata } from '../types';

interface SegmentGeneratorStoreState {
  isLoading: boolean;
  segments: SegmentMetadata[];
}

class SegmentGeneratorStore {
  private privateState: SegmentGeneratorStoreState = reactive<SegmentGeneratorStoreState>({
    isLoading: false,
    segments: [],
  });

  readonly state = computed(() => readonly(this.privateState));

  private loadSegmentsAbort?: AbortController;

  private loadSegmentsPromise?: Promise<SegmentMetadata[]>;

  private fetchedSiteId?: string|number;

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
    });
  }
}

export default new SegmentGeneratorStore();

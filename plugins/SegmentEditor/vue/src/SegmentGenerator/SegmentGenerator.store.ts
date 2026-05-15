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

  loadSegments(
    siteId?: string|number,
    visitSegmentsOnly?: boolean,
  ): Promise<DeepReadonly<SegmentMetadata[]>> {
    // Do not cache the in-flight promise. AjaxHelper silently swallows
    // aborts (when globalAjaxQueue.abort() fires during navigation), which
    // means a cached promise can stay pending forever and any subsequent
    // call returns that stuck promise so queriedSegments never populates
    // and the segment editor form fails to render any condition rows.
    this.privateState.isLoading = true;

    let idSites: string|number|undefined = undefined;
    let idSite: string|number|undefined = undefined;

    if (siteId === 'all' || !siteId) {
      idSites = 'all';
      idSite = 'all';
    } else if (siteId) {
      idSites = siteId;
      idSite = siteId;
    }

    return AjaxHelper.fetch<SegmentMetadata[]>({
      method: 'API.getSegmentsMetadata',
      filter_limit: '-1',
      _hideImplementationData: 0,
      idSites,
      idSite,
    }, {
      // Stay out of globalAjaxQueue so a navigation-triggered
      // globalAjaxQueue.abort() (e.g. when the panel close re-renders
      // hashchange listeners) cannot kill the metadata fetch.
      // AjaxHelper silently swallows aborts, which would leave the
      // promise pending forever and the segment editor form rendered
      // without dimension labels or condition rows.
      abortable: false,
    }).then((response) => {
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

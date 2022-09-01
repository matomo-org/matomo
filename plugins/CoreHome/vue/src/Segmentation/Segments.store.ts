/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive, readonly, DeepReadonly } from 'vue';
import Matomo from '../Matomo/Matomo';

interface SegmentInfo {
  definition: string;
  name: string;
}

interface SegmentsStoreData {
  availableSegments: SegmentInfo[];
}

class SegmentsStore {
  private segmentState = reactive<SegmentsStoreData>({
    availableSegments: [],
  });

  get state(): DeepReadonly<SegmentsStoreData> {
    return readonly(this.segmentState);
  }

  constructor() {
    Matomo.on('piwikSegmentationInited', () => this.setSegmentState());
  }

  private setSegmentState() {
    try {
      const uiControlObject = $('.segmentEditorPanel').data('uiControlObject');
      this.segmentState.availableSegments = uiControlObject.impl.availableSegments || [];
    } catch (e) {
      // segment editor is not initialized yet
    }
  }
}

export default new SegmentsStore();

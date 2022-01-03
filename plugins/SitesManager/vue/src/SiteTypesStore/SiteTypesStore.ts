/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  reactive,
  readonly,
  computed,
} from 'vue';
import { AjaxHelper, MatomoUrl, lazyInitSingleton } from 'CoreHome';
import SiteType from './SiteType';

interface SiteTypesStoreState {
  typesById: Record<string, SiteType>;
}

type AvailableTypesResponse = SiteType[];

const { $ } = window;

class SiteTypesStore {
  private state = reactive<SiteTypesStoreState>({
    typesById: {},
  });

  public readonly typesById = computed(() => readonly(this.state).typesById);

  private response?: Promise<SiteTypesStore['typesById']['value']>;

  constructor() {
    this.fetchAvailableTypes();
  }

  public fetchAvailableTypes(): Promise<SiteTypesStore['typesById']['value']> {
    if (this.response) {
      return Promise.resolve(this.response);
    }

    this.response = AjaxHelper.fetch<AvailableTypesResponse>({
      method: 'API.getAvailableMeasurableTypes',
      filter_limit: '-1',
    }).then((types) => {
      types.forEach((type) => {
        this.state.typesById[type.id] = type;
      });

      return this.typesById.value;
    });

    return this.response;
  }

  public getEditSiteIdParameter(): string|undefined {
    const editsiteid = MatomoUrl.hashParsed.value.editsiteid as string;
    if (editsiteid && $.isNumeric(editsiteid) && !MatomoUrl.hashParsed.value.showaddsite) {
      return editsiteid;
    }
    return undefined;
  }

  public removeEditSiteIdParameterFromHash(): void {
    const params = { ...MatomoUrl.hashParsed.value };
    delete params.editsiteid;
    MatomoUrl.updateHash(params);
  }
}

export default lazyInitSingleton(SiteTypesStore);

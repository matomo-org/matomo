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
import { AjaxHelper, MatomoUrl } from 'CoreHome';
import SiteType from './SiteType';

interface SiteTypesStoreState {
  isLoading: boolean;
  typesById: Record<string, SiteType>;
}

type AvailableTypesResponse = SiteType[];

const { $ } = window;

class SiteTypesStore {
  private state = reactive<SiteTypesStoreState>({
    isLoading: false,
    typesById: {},
  });

  readonly typesById = computed(() => readonly(this.state).typesById);

  readonly isLoading = computed(() => readonly(this.state).isLoading);

  readonly types = computed(() => Object.values(this.typesById.value));

  private response?: Promise<SiteTypesStore['types']['value']>;

  init() {
    return this.fetchAvailableTypes();
  }

  fetchAvailableTypes(): Promise<SiteTypesStore['types']['value']> {
    if (this.response) {
      return Promise.resolve(this.response);
    }

    this.state.isLoading = true;
    this.response = AjaxHelper.fetch<AvailableTypesResponse>({
      method: 'API.getAvailableMeasurableTypes',
      filter_limit: '-1',
    }).then((types) => {
      types.forEach((type) => {
        this.state.typesById[type.id] = type;
      });

      return this.types.value;
    }).finally(() => {
      this.state.isLoading = false;
    });

    return this.response;
  }

  getEditSiteIdParameter(): string|undefined {
    // parse query directly because #/editsiteid=N was supported alongside #/?editsiteid=N
    const m = MatomoUrl.hashQuery.value.match(/editsiteid=([0-9]+)/);
    if (!m) {
      return undefined;
    }

    const isShowAddSite = MatomoUrl.urlParsed.value.showaddsite === '1'
      || MatomoUrl.urlParsed.value.showaddsite === 'true';

    const editsiteid = m[1];
    if (editsiteid && $.isNumeric(editsiteid) && !isShowAddSite) {
      return editsiteid;
    }

    return undefined;
  }

  removeEditSiteIdParameterFromHash(): void {
    const params = { ...MatomoUrl.hashParsed.value };
    delete params.editsiteid;
    MatomoUrl.updateHash(params);
  }
}

export default new SiteTypesStore();

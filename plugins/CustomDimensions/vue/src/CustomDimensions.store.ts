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
import { CustomDimension, AvailableScope, ExtractionDimension } from './types';

interface CustomDimensionsStoreState {
  isLoading: boolean;
  isUpdating: boolean;
  customDimensions: CustomDimension[];
  availableScopes: AvailableScope[];
  extractionDimensions: ExtractionDimension[];
}

class CustomDimensionsStore {
  private privateState = reactive<CustomDimensionsStoreState>({
    customDimensions: [],
    availableScopes: [],
    extractionDimensions: [],
    isLoading: false,
    isUpdating: false,
  });

  private state = computed(() => readonly(this.privateState));

  readonly isLoading = computed(() => this.state.value.isLoading);

  readonly isUpdating = computed(() => this.state.value.isUpdating);

  readonly extractionDimensions = computed(() => this.state.value.extractionDimensions);

  readonly extractionDimensionsOptions = computed(
    () => this.extractionDimensions.value.map((e) => ({ key: e.value, value: e.name })),
  );

  readonly availableScopes = computed(() => this.state.value.availableScopes);

  readonly customDimensions = computed(() => this.state.value.customDimensions);

  readonly customDimensionsById = computed(() => {
    const dimensionsById: Record<string, DeepReadonly<CustomDimension>> = {};
    this.customDimensions.value.forEach((c) => {
      dimensionsById[`${c.idcustomdimension}`] = c;
    });
    return dimensionsById;
  });

  private reloadPromise: Promise<void>|null = null;

  reload() {
    this.privateState.customDimensions = [];
    this.privateState.availableScopes = [];
    this.privateState.extractionDimensions = [];
    this.reloadPromise = null;
    return this.fetch();
  }

  fetch() {
    if (this.reloadPromise) {
      return this.reloadPromise;
    }

    this.privateState.isLoading = true;
    this.reloadPromise = Promise.all([
      this.fetchConfiguredCustomDimensions(),
      this.fetchAvailableExtractionDimensions(),
      this.fetchAvailableScopes(),
    ]).finally(() => {
      this.privateState.isLoading = false;
    }) as unknown as Promise<void>;

    return this.reloadPromise!;
  }

  fetchConfiguredCustomDimensions() {
    return AjaxHelper.fetch<CustomDimension[]>({
      method: 'CustomDimensions.getConfiguredCustomDimensions',
      filter_limit: '-1',
    }).then((r) => {
      this.privateState.customDimensions = r;
    });
  }

  fetchAvailableExtractionDimensions() {
    return AjaxHelper.fetch<ExtractionDimension[]>({
      method: 'CustomDimensions.getAvailableExtractionDimensions',
      filter_limit: '-1',
    }).then((r) => {
      this.privateState.extractionDimensions = r;
    });
  }

  fetchAvailableScopes() {
    return AjaxHelper.fetch<AvailableScope[]>({
      method: 'CustomDimensions.getAvailableScopes',
      filter_limit: '-1',
    }).then((r) => {
      this.privateState.availableScopes = r;
    });
  }

  createOrUpdateDimension(dimension: CustomDimension, method: string): Promise<void> {
    this.privateState.isUpdating = true;
    return AjaxHelper.post(
      {
        method,
        scope: dimension.scope,
        idDimension: dimension.idcustomdimension,
        idSite: dimension.idsite,
        name: dimension.name,
        active: dimension.active ? '1' : '0',
        caseSensitive: dimension.case_sensitive ? '1' : '0',
      },
      {
        extractions: dimension.extractions,
      },
    ).finally(() => {
      this.privateState.isUpdating = false;
    });
  }
}

export default new CustomDimensionsStore();

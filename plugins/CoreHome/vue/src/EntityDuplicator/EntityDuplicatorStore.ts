/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive } from 'vue';
import { translateOrDefault } from '../translate';
import Matomo from '../Matomo/Matomo';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import {
  BaseDuplicatorAdapter,
  EntityDuplicatorAdapter,
  EntityDuplicatorAdapterProperties,
} from './EntityDuplicatorAdapter';

interface EntityDuplicatorState {
  /**
   * Whether the modal is currently visible
   */
  isModalVisible: boolean
  /**
   * Form data that needs to be included in the request sent to the server but won't change between
   * requests. This could be a parent ID (e.g. ID of the container when duplicating MTM tags).
   */
  commonFormData?: Record<string, unknown>;
  /**
   * Additional form data that needs to be included in the request sent to the server. This should
   * typically include the unique identifier of the entity being copied (e.g. idGoal for a goal).
   */
  entityFormData: Record<string, unknown>;
  /**
   * Translation of what is being copied (e.g. goal, funnel, segment, ...). This can be a string
   * or translation key. If nothing is provided 'report' is used.
   */
  entityTypeTranslation: string;
}

export class EntityDuplicatorStore {
  state: EntityDuplicatorState = reactive({
    isModalVisible: false,
    commonFormData: {},
    entityFormData: {},
    entityTypeTranslation: '',
  });

  /**
   * The adapter class defines the implementation/behaviour of common part of the duplication
   * process such as validation, gathering parameters, posting to the API, and handling success.
   */
  adapter: EntityDuplicatorAdapter;

  /**
   * Protected so that the buildStoreInstance has to be used. This ensures that the modal store is
   * instantiated as a reactive object. See buildStoreInstance for more documentation.
   *
   * @param duplicateEntityTypeTranslation
   * @param adapterDefinition
   * @param commonFormData
   * @protected
   */
  protected constructor(
    duplicateEntityTypeTranslation: string,
    adapterDefinition: EntityDuplicatorAdapter | EntityDuplicatorAdapterProperties,
    commonFormData?: Record<string, unknown>,
  ) {
    this.state.entityTypeTranslation = duplicateEntityTypeTranslation;
    this.adapter = 'validateFormFields' in adapterDefinition ? adapterDefinition
      : new BaseDuplicatorAdapter(adapterDefinition as EntityDuplicatorAdapterProperties);
    this.state.commonFormData = commonFormData ?? {};
  }

  /**
   * Returns a reactive store object for the specific type of entity being copied so that it can be
   * used to maintain the state of the modal across all the actions which trigger showing the modal.
   * See the property descriptions of the EntityDuplicatorState interface for more information.
   *
   * @param duplicateEntityTypeTranslation Translation string or translated string of the item being
   * duplicated. E.g. goal, funnel, heatmap,...
   * @param adapterDefinition Either an instance of EntityDuplicatorAdapter or an object containing
   * the properties necessary to instantiate an instance of the default BaseDuplicatorAdapter. This
   * allows encapsulating the desired implementation of how the modal behaves such as validation
   * and posting the API request.
   * @param commonFormData Optional form data that's common to the type of entity being duplicated.
   * E.g. status to set for the new copies or something similar.
   */
  static buildStoreInstance(
    duplicateEntityTypeTranslation: string,
    adapterDefinition: EntityDuplicatorAdapter | EntityDuplicatorAdapterProperties,
    commonFormData?: Record<string, unknown>,
  ): EntityDuplicatorStore {
    return reactive(new EntityDuplicatorStore(
      duplicateEntityTypeTranslation,
      adapterDefinition,
      commonFormData,
    ));
  }

  showModal(entityFormData?: Record<string, unknown>): void {
    // Make sure that we start fresh
    this.resetFormData();

    // Update the store with any provided form data
    Object.entries(entityFormData ?? {}).forEach(([key, value]) => {
      this.state.entityFormData[key] = value;
    });

    this.state.isModalVisible = true;
  }

  hideModal(): void {
    this.state.isModalVisible = false;
    this.resetFormData();
  }

  resetFormData(): void {
    // Remove all properties (preserves the original object reference)
    Object.keys(this.state.entityFormData).forEach((key) => {
      delete this.state.entityFormData[key];
    });
  }

  getFormValues(idDestinationSites?: number|string|[]): Record<string, unknown> {
    const idDestinationSitesArray = Array.isArray(idDestinationSites)
      ? idDestinationSites : [] as number[];
    if (idDestinationSites && !Array.isArray(idDestinationSites)) {
      idDestinationSitesArray.push(idDestinationSites as number);
    }
    return {
      idSite: Matomo.idSite || MatomoUrl.parsed.value.idSite,
      idDestinationSites: idDestinationSitesArray,
      ...this.state.commonFormData,
      ...this.state.entityFormData,
    } as Record<string, unknown>;
  }

  /**
   * Uses the entityTypeTranslation property to return the translated entity type (e.g.
   * goal, funnel, segment, ...), which can be a translated string or translation key. If the value
   * is a translation key, the translated value will be returned. If no value is set, the default is
   * the translation of 'report'.
   */
  get getEntityTypeTranslation(): string {
    // Default to 'report' if no value is provided via entityTypeTranslation
    let translationKey = 'CoreHome_ReportLowercase';
    if (this.state.entityTypeTranslation) {
      translationKey = this.state.entityTypeTranslation;
    }

    // Only translate if it's a translation key and not an already translated string
    return translateOrDefault(translationKey);
  }
}

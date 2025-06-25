/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive } from 'vue';
import { translateOrDefault } from '../translate';

interface MatomoCopyState {
  /**
   * Whether the modal is currently visible
   */
  isModalVisible: boolean
  /**
   * Whether the watching is suppressed. This allows updating the form data without validation.
   */
  isWatchSuppressed: boolean
  /**
   * Form data that needs to be included in the request sent to the server but won't change between
   * requests. This could be a parent ID (e.g. ID of the container when copying MTM tags).
   */
  commonFormData?: Record<string, unknown>;
  /**
   * Additional form data that needs to be included in the request sent to the server. This should
   * typically include the unique identifier of the entity being copied (e.g. idGoal for a goal).
   */
  entityFormData: Record<string, unknown>;
  /**
   * Should uniquely identify what is being copied (e.g. goal, funnel, segment, ...). The is
   * important as it's used as the entityTypeName property of the request sent to the server.
   */
  copyEntityType: string;
  /**
   * Translation of what is being copied (e.g. goal, funnel, segment, ...). This can be a string
   * or translation key. If nothing is provided 'report' is used.
   */
  copyEntityTypeTranslation: string;
}

export class MatomoCopyModalStore {
  state: MatomoCopyState = reactive({
    isModalVisible: false,
    isWatchSuppressed: false,
    commonFormData: {},
    entityFormData: {},
    copyEntityType: '',
    copyEntityTypeTranslation: '',
  });

  constructor(
    copyEntityType: string,
    copyEntityTypeTranslation: string,
    commonFormData?: Record<string, unknown>,
  ) {
    this.state.copyEntityType = copyEntityType;
    this.state.copyEntityTypeTranslation = copyEntityTypeTranslation;
    this.state.commonFormData = commonFormData ?? {};
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
    this.state.isWatchSuppressed = true;

    // Remove all properties (preserves the original object reference)
    Object.keys(this.state.entityFormData).forEach((key) => {
      delete this.state.entityFormData[key];
    });
  }

  disableWatchSuppression(): void {
    this.state.isWatchSuppressed = false;
  }

  /**
   * Uses the copyEntityTypeTranslation property to return the translated entity type (e.g. goal,
   * funnel, segment, ...), which can be a translated string or translation key. If the value is a
   * translation key, the translated value will be returned. If no value is set, the default is \
   * the translation of 'report'.
   */
  get getEntityTypeTranslation(): string {
    // Default to 'report' if no value is provided via copyEntityTypeTranslation
    let translationKey = 'CoreHome_ReportLowercase';
    if (this.state.copyEntityTypeTranslation) {
      translationKey = this.state.copyEntityTypeTranslation;
    }

    // Only translate if it's a translation key and not an already translated string
    return translateOrDefault(translationKey);
  }
}

export function buildMatomoCopyModalStore(
  copyEntityType: string,
  copyEntityTypeTranslation: string,
  commonFormData?: Record<string, unknown>,
): MatomoCopyModalStore {
  return reactive(new MatomoCopyModalStore(
    copyEntityType,
    copyEntityTypeTranslation,
    commonFormData,
  ));
}

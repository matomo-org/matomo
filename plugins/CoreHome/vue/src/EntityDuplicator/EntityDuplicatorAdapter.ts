/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Matomo from '../Matomo/Matomo';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import { translate } from '../translate';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import { NotificationsStore } from '../Notification';

export interface DuplicateRequestResponse {
  success: boolean;
  message?: string;
  additionalData?: Record<string, unknown>;
}

export interface ValidationResult {
  isValid: boolean;
  errorMessages: string[];
}

export interface EntityDuplicatorAdapterProperties {
  module?: string;
  method: string;
  format?: string;
  requiredFields?: string[];
}

export interface EntityDuplicatorAdapter {
  /**
   * Validates the form fields before submission. Optionally return promise in case API request is
   * needed for validation.
   */
  validateFormFields(
    formValues: Record<string, unknown>,
  ): ValidationResult | Promise<ValidationResult>;

  /**
   * Prepares the API parameters for the duplication request. Optionally return promise in case API
   * request is needed for to prepare all parameters.
   */
  prepareApiParams(formValues: Record<string, unknown>): QueryParameters | Promise<QueryParameters>;

  /**
   * Submits the duplication request to the server and returns a promise for the modal to wait for
   */
  submitRequest(params: QueryParameters): Promise<DuplicateRequestResponse>;

  /**
   * Optional: Called after successful duplication
   */
  onSuccess?(response: DuplicateRequestResponse): void;

  /**
   * Optional: Called after failed duplication
   */
  onFailure?(error: DuplicateRequestResponse | Error): void;

  /**
   * Optional: Called before showing the modal and can be used to show the modal as loading if
   * there are any async operations needed to finish presenting the modal.
   */
  beforeShowModal?(): void | Promise<void>;
}

export class BaseDuplicatorAdapter implements EntityDuplicatorAdapter {
  module: string;

  method: string;

  format: string;

  requiredFields: string[];

  constructor(properties: EntityDuplicatorAdapterProperties) {
    this.module = properties.module || 'API';
    this.method = properties.method;
    this.format = properties.format || 'json';
    this.requiredFields = properties.requiredFields || ['idSite', 'idDestinationSites'];
  }

  async validateFormFields(
    formValues: Record<string, unknown>,
  ): Promise<ValidationResult> {
    const errorMessages: string[] = [];

    this.requiredFields.forEach((fieldName) => {
      if (!(fieldName in formValues) || !formValues[fieldName]) {
        errorMessages.push(translate('General_Required', fieldName));
      }
    });

    return new Promise((resolve) => resolve({
      errorMessages,
      isValid: errorMessages.length === 0,
    } as ValidationResult));
  }

  prepareApiParams(formValues: Record<string, unknown>): QueryParameters {
    return {
      idSite: Matomo.idSite || MatomoUrl.parsed.value.idSite,
      idDestinationSites: [formValues.idDestinationSite as number|string],
      ...formValues,
    };
  }

  async submitRequest(params: QueryParameters): Promise<DuplicateRequestResponse> {
    // Override the defaults if provided in the params and then remove them from the params
    this.module = params.module || this.module;
    this.method = params.method || this.method;
    this.format = params.format || this.format;
    const postParams = params as Omit<QueryParameters, 'module' | 'method' | 'format'>;

    if (!this.method || this.method.length < 1) {
      throw new Error('The POST method cannot be empty!');
    }

    const ajax = new AjaxHelper();
    // Force callback but leave it empty so that API errors are only displayed in the modal
    ajax.useCallbackInCaseOfError();
    ajax.setErrorCallback(null);
    // Remove some default parameters as they aren't applicable to copying existing reports
    ajax.removeDefaultParameter('date');
    ajax.removeDefaultParameter('period');
    ajax.removeDefaultParameter('segment');
    // Set the main params as part of the URL
    ajax.addParams({
      module: this.module,
      method: this.method,
      format: this.format,
    }, 'GET');
    ajax.addParams(postParams, 'POST');
    ajax.setFormat(this.format);
    return ajax.send();
  }

  /**
   * Optional: Called during onSuccess method if both are defined. This allows using the default
   * success method while also defining some custom actions such as reloading a store
   */
  onSuccessCallback?(response: DuplicateRequestResponse): Promise<void>;

  onSuccess(response: DuplicateRequestResponse): void {
    // In case a promise wasn't returned, wrap the result with a promise for consistent processing
    let onSuccessCallbackPromise = new Promise((resolve) => resolve()) as Promise<void>;
    if (this.onSuccessCallback) {
      onSuccessCallbackPromise = this.onSuccessCallback(response);
    }

    onSuccessCallbackPromise.then(() => {
      // Show the success message returned by the API
      setTimeout(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: response.message as string,
          context: response.success ? 'success' : 'error',
          type: 'toast',
          id: 'entityDuplicationResult',
        });
        NotificationsStore.scrollToNotification(notificationInstanceId);
      });
    });
  }
}

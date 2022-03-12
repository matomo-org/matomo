/*!
* Matomo - free/libre analytics platform
*
* @link https://matomo.org
* @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

import { AjaxOptions } from 'CoreHome';

// the piwikApi angularjs service is passed in some frontend events to allow plugins to modify
// a request before it is sent. for the time being in Vue we use this mock, which has the same
// API as the piwikApi service, to modify the input used with AjaxHelper. this provides BC
// with for plugins that haven't been converted.
//
// should be removed in Matomo 5.
export default class PiwikApiMock {
  constructor(private parameters: QueryParameters, private options: AjaxOptions) {}

  addParams(params: QueryParameters): void {
    Object.assign(this.parameters, params);
  }

  withTokenInUrl(): void {
    this.options.withTokenInUrl = true;
  }

  reset(): void {
    Object.keys(this.parameters).forEach((name) => {
      delete this.parameters[name];
    });

    delete this.options.postParams;
  }

  addPostParams(params: QueryParameters): void {
    this.options.postParams = { ...this.options.postParams, ...params };
  }
}

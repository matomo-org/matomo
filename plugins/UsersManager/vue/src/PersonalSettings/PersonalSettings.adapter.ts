/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import PersonalSettings from './PersonalSettings.vue';

export default createAngularJsAdapter({
  component: PersonalSettings,
  scope: {
    isUsersAdminEnabled: {
      angularJsBind: '<',
    },
    title: {
      angularJsBind: '<',
    },
    userLogin: {
      angularJsBind: '<',
    },
    userEmail: {
      angularJsBind: '<',
    },
    currentLanguageCode: {
      angularJsBind: '<',
    },
    languageOptions: {
      angularJsBind: '<',
    },
    currentTimeformat: {
      angularJsBind: '<',
    },
    timeFormats: {
      angularJsBind: '<',
    },
    defaultReport: {
      angularJsBind: '<',
    },
    defaultReportOptions: {
      angularJsBind: '<',
    },
    defaultReportIdSite: {
      angularJsBind: '<',
    },
    defaultReportSiteName: {
      angularJsBind: '<',
    },
    defaultDate: {
      angularJsBind: '<',
    },
    availableDefaultDates: {
      angularJsBind: '<',
    },
  },
  directiveName: 'matomoPersonalSettings',
});

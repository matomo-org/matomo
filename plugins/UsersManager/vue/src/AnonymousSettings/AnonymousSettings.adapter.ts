/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import AnonymousSettings from './AnonymousSettings.vue';

export default createAngularJsAdapter({
  component: AnonymousSettings,
  scope: {
    title: {
      angularJsBind: '<',
    },
    anonymousSites: {
      angularJsBind: '<',
    },
    anonymousDefaultReport: {
      angularJsBind: '<',
    },
    anonymousDefaultSite: {
      angularJsBind: '<',
    },
    anonymousDefaultDate: {
      angularJsBind: '<',
    },
    availableDefaultDates: {
      angularJsBind: '<',
    },
    defaultReportOptions: {
      angularJsBind: '<',
    },
  },
  directiveName: 'matomoAnonymousSettings',
});

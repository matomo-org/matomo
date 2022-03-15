/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import Geoip2Updater from './Geoip2Updater.vue';

export default createAngularJsAdapter({
  component: Geoip2Updater,
  scope: {
    geoIPDatabasesInstalled: {
      angularJsBind: '<',
    },
    showGeoIPUpdateSection: {
      angularJsBind: '<',
    },
    dbipLiteUrl: {
      angularJsBind: '<',
    },
    dbipLiteFilename: {
      angularJsBind: '<',
    },
    geoIPLocUrl: {
      angularJsBind: '<',
    },
    isProviderPluginActive: {
      angularJsBind: '<',
    },
    geoIPIspUrl: {
      angularJsBind: '<',
    },
    lastTimeUpdaterRun: {
      angularJsBind: '<',
    },
    geoIPUpdatePeriod: {
      angularJsBind: '<',
    },
    updatePeriodOptions: {
      angularJsBind: '<',
    },
  },
  directiveName: 'piwikGeoip2Updater',
  transclude: true,
});

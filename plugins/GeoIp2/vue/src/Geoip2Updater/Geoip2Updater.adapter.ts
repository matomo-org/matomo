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
    geoIpDatabasesInstalled: {
      angularJsBind: '<',
    },
    showGeoIpUpdateSection: {
      angularJsBind: '<',
    },
    dbipLiteUrl: {
      angularJsBind: '<',
    },
    dbipLiteFilename: {
      angularJsBind: '<',
    },
    geoipLocUrl: {
      angularJsBind: '<',
    },
    isProviderPluginActive: {
      angularJsBind: '<',
    },
    geoipIspUrl: {
      angularJsBind: '<',
    },
    lastTimeUpdaterRun: {
      angularJsBind: '<',
    },
    geoipUpdatePeriod: {
      angularJsBind: '<',
    },
    updatePeriodOptions: {
      angularJsBind: '<',
    },
    geoipDatabaseStartedInstalled: {
      angularJsBind: '<',
    },
    showGeoipUpdateSection: {
      angularJsBind: '<',
    },
    nextRunTime: {
      angularJsBind: '<',
    },
    nextRunTimePretty: {
      angularJsBind: '<',
    },
  },
  directiveName: 'piwikGeoip2Updater',
  transclude: true,
});

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import ArchivingSettings from './ArchivingSettings.vue';

export default createAngularJsAdapter({
  component: ArchivingSettings,
  scope: {
    enableBrowserTriggerArchiving: {
      angularJsBind: '<',
    },
    showSegmentArchiveTriggerInfo: {
      angularJsBind: '<',
    },
    isGeneralSettingsAdminEnabled: {
      angularJsBind: '<',
    },
    showWarningCron: {
      angularJsBind: '<',
    },
    todayArchiveTimeToLive: {
      angularJsBind: '<',
    },
    todayArchiveTimeToLiveDefault: {
      angularJsBind: '<',
    },
  },
  directiveName: 'matomoArchivingSettings',
});

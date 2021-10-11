/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import './MatomoUrl/MatomoUrl.adapter';
import './Matomo/Matomo.adapter';
import './noAdblockFlag';
import './Periods/Day';
import './Periods/Week';
import './Periods/Month';
import './Periods/Year';
import './Periods/Range';
import './Periods/Periods.adapter';
import './AjaxHelper/AjaxHelper.adapter';
import './PiwikUrl/PiwikUrl.adapter';
import './Piwik/Piwik.adapter';
import './MatomoDialog/MatomoDialog.adapter';

export { default as activityIndicatorAdapter } from './ActivityIndicator/ActivityIndicator.adapter';
export { default as ActivityIndicator } from './ActivityIndicator/ActivityIndicator.vue';
export { default as translate } from './translate';
export { default as alertAdapter } from './Alert/Alert.adapter';
export { default as AjaxHelper } from './AjaxHelper/AjaxHelper';
export { default as MatomoUrl } from './MatomoUrl/MatomoUrl';
export { default as Matomo } from './Matomo/Matomo';
export * from './Periods';
export { default as MatomoDialog } from './MatomoDialog/MatomoDialog.vue';

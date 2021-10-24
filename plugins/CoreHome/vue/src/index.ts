/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import './noAdblockFlag';
import './MatomoUrl/MatomoUrl.adapter';
import './Matomo/Matomo.adapter';
import './Periods/Day';
import './Periods/Week';
import './Periods/Month';
import './Periods/Year';
import './Periods/Range';
import './Periods/Periods.adapter';
import './AjaxHelper/AjaxHelper.adapter';
import './MatomoDialog/MatomoDialog.adapter';
import './EnrichedHeadline/EnrichedHeadline.adapter';
import './ContentBlock/ContentBlock.adapter';
import './Comparisons/Comparisons.adapter';
import './DatePicker/DatePicker.adapter';
import './DateRangePicker/DateRangePicker.adapter';

export { default as createAngularJsAdapter } from './createAngularJsAdapter';
export { default as activityIndicatorAdapter } from './ActivityIndicator/ActivityIndicator.adapter';
export { default as ActivityIndicator } from './ActivityIndicator/ActivityIndicator.vue';
export { default as translate } from './translate';
export { default as alertAdapter } from './Alert/Alert.adapter';
export { default as AjaxHelper } from './AjaxHelper/AjaxHelper';
export { default as MatomoUrl } from './MatomoUrl/MatomoUrl';
export { default as Matomo } from './Matomo/Matomo';
export * from './Periods';
export { default as MatomoDialog } from './MatomoDialog/MatomoDialog.vue';
export { default as EnrichedHeadline } from './EnrichedHeadline/EnrichedHeadline.vue';
export { default as ContentBlock } from './ContentBlock/ContentBlock.vue';
export { default as Comparisons } from './Comparisons/Comparisons.vue';
export { default as DatePicker } from './DatePicker/DatePicker.vue';
export { default as DateRangePicker } from './DateRangePicker/DateRangePicker.vue';

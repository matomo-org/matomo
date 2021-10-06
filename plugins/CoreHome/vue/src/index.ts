/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import './Periods/Day';
import './Periods/Week';
import './Periods/Month';
import './Periods/Year';
import './Periods/Range';
import './Periods/Periods.adapter';

export { default as activityIndicatorAdapter } from './ActivityIndicator/ActivityIndicator.adapter';
export { default as ActivityIndicator } from './ActivityIndicator/ActivityIndicator.vue';
export { default as translate } from './translate';
export { default as alertAdapter } from './Alert/Alert.adapter';
export { default as Periods } from './Periods/Periods';

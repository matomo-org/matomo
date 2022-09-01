/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import TrackingFailures from './TrackingFailures.vue';

export default createAngularJsAdapter({
  component: TrackingFailures,
  directiveName: 'matomoTrackingFailures',
});

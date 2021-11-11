/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import QuickAccess from './QuickAccess.vue';

export default createAngularJsAdapter({
  component: QuickAccess,
  directiveName: 'piwikQuickAccess',
});

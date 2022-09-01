/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import SmtpSettings from './SmtpSettings.vue';

export default createAngularJsAdapter({
  component: SmtpSettings,
  scope: {
    mail: {
      angularJsBind: '<',
    },
    mailTypes: {
      angularJsBind: '<',
    },
    mailEncryptions: {
      angularJsBind: '<',
    },
  },
  directiveName: 'matomoSmtpSettings',
});

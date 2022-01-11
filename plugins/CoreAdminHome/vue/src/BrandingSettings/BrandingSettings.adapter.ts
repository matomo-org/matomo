/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import BrandingSettings from './BrandingSettings.vue';

export default createAngularJsAdapter({
  component: BrandingSettings,
  scope: {
    fileUploadEnabled: {
      angularJsBind: '<',
    },
    logosWriteable: {
      angularJsBind: '<',
    },
    useCustomLogo: {
      angularJsBind: '<',
    },
    pathUserLogoDirectory: {
      angularJsBind: '<',
    },
    pathUserLogo: {
      angularJsBind: '<',
    },
    pathUserLogoSmall: {
      angularJsBind: '<',
    },
    pathUserLogoSvg: {
      angularJsBind: '<',
    },
    hasUserLogo: {
      angularJsBind: '<',
    },
    pathUserFavicon: {
      angularJsBind: '<',
    },
    hasUserFavicon: {
      angularJsBind: '<',
    },
    isPluginsAdminEnabled: {
      angularJsBind: '<',
    },
  },
  directiveName: 'matomoBrandingSettings',
});

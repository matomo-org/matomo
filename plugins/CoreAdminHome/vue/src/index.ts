/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

import './ArchivingSettings/ArchivingSettings.adapter';
import './BrandingSettings/BrandingSettings.adapter';
import './SmtpSettings/SmtpSettings.adapter';
import './JsTrackingCodeGenerator/JsTrackingCodeGenerator.adapter';
import './ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.adapter';
import './TrackingFailures/TrackingFailures.adapter';

export { default as ArchivingSettings } from './ArchivingSettings/ArchivingSettings.vue';
export { default as BrandingSettings } from './BrandingSettings/BrandingSettings.vue';
export { default as SmtpSettings } from './SmtpSettings/SmtpSettings.vue';
export {
  default as JsTrackingCodeGenerator,
} from './JsTrackingCodeGenerator/JsTrackingCodeGenerator.vue';
export {
  default as JsTrackingCodeGeneratorSitesWithoutData,
} from './JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue';
export {
  default as ImageTrackingCodeGenerator,
} from './ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue';
export { default as TrackingFailures } from './TrackingFailures/TrackingFailures.vue';

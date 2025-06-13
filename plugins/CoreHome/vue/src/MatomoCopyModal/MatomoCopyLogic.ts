/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { translateOrDefault } from '../translate';

export default {
  computed: {
    /**
     * Uses the copyEntityTypeTranslation property to return the translated entity type (e.g. goal,
     * funnel, segment, ...), which can be a translated string or translation key. If the value is a
     * translation key, the translated value will be returned. If no value is set, the default is \
     * the translation of 'report'.
     */
    getEntityTypeTranslation(this: { copyEntityTypeTranslation: string }): string {
      // Default to 'report' if no value is provided via copyEntityTypeTranslation
      let translationKey = 'CoreHome_ReportLowercase';
      if (this.copyEntityTypeTranslation) {
        translationKey = this.copyEntityTypeTranslation;
      }

      // Only translate if it's a translation key and not an already translated string
      return translateOrDefault(translationKey);
    },
  },
};

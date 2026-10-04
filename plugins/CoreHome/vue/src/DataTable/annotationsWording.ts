/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { translate } from '../translate';

// What the toggle says it will do. The promoted control has no room to write it, so it carries the
// same words in its title instead of inventing its own.
export default function annotationsWording(showing: boolean): string {
  return showing
    ? translate('Annotations_HideAnnotations')
    : translate('Annotations_ShowAnnotations');
}

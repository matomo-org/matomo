/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { Matomo, translate } from 'CoreHome';

/** The message of a failed API call, for display as text: the API sends it HTML-escaped. */
export default function apiErrorText(error: unknown): string {
  const message = error instanceof Error ? error.message : '';
  return message ? Matomo.helper.htmlDecode(message) : translate('General_ErrorRequest', '', '');
}

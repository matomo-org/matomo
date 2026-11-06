/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { MatomoUrl } from 'CoreHome';

/** Without the Marketplace's own parameters, which would only clutter that page's URL. */
export default function manageRequestsUrl(): string {
  const { idSite, period, date } = MatomoUrl.urlParsed.value;

  return `?${MatomoUrl.stringify({
    module: 'Marketplace',
    action: 'manageRequests',
    idSite: idSite || MatomoUrl.parsed.value.idSite,
    period,
    date,
  })}`;
}

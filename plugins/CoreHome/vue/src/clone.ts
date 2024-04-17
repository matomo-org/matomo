/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export default function clone<T>(p: T): T {
  if (typeof p === 'undefined') {
    return p;
  }

  return JSON.parse(JSON.stringify(p)) as T;
}

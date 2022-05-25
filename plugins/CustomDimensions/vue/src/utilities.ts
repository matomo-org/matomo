/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export function ucfirst(s: string): string {
  return `${s[0].toUpperCase()}${s.slice(1)}`;
}

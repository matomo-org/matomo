/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export function adjustHourToTimezone(hour: string, difference: number): string {
  return `${(24 + parseFloat(hour) + difference) % 24}`;
}

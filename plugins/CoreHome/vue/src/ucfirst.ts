/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Uppercase the first character of a string, leaving the rest untouched (e.g. "visits" ->
 * "Visits"). Mirrors PHP's ucfirst(); an empty or missing value yields an empty string.
 */
export default function ucfirst(text?: string): string {
  return text ? text.charAt(0).toUpperCase() + text.slice(1) : '';
}

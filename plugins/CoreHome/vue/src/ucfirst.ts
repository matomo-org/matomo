/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Uppercase the first character of a string, leaving the rest untouched (e.g. "visits" ->
 * "Visits"). Uses locale-aware Unicode casing; an empty or missing value yields an empty string.
 */
export default function ucfirst(text?: string, locale?: string): string {
  if (!text) {
    return '';
  }

  const [firstCharacter, ...remainingCharacters] = Array.from(text);
  return firstCharacter.toLocaleUpperCase(locale || undefined) + remainingCharacters.join('');
}

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export default function translate(
  translationStringId: string,
  ...values: (string|string[])[]
): string {
  let pkArgs = values as string[];
  // handle variadic args AND single array of values (to match _pk_translate signature)
  if (values.length === 1 && values[0] && Array.isArray(values[0])) {
    [pkArgs] = values as string[][];
  }
  return window._pk_translate(translationStringId, pkArgs); // eslint-disable-line
}

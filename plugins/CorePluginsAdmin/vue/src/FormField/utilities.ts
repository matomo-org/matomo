/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

interface Option {
  key: string|number;
  value: unknown;
}

export function processCheckboxAndRadioAvailableValues(
  availableValues: Record<string, unknown>|null,
  type: string,
): Option[] {
  if (!availableValues) {
    return [];
  }

  const flatValues: Option[] = [];
  Object.entries(availableValues).forEach(([valueObjKey, value]) => {
    if (value && typeof value === 'object' && typeof (value as Option).key !== 'undefined') {
      flatValues.push(value as Option);
      return;
    }

    let key: number|string = valueObjKey;
    if (type === 'integer' && typeof valueObjKey === 'string') {
      key = parseInt(key, 10);
    }

    flatValues.push({ key, value });
  });

  return flatValues;
}

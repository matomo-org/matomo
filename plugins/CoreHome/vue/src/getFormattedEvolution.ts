/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Matomo from './Matomo/Matomo';

function calculateEvolution(currentValue: string|number, pastValue: string|number) {
  const pastValueParsed = parseInt(pastValue as string, 10);
  const currentValueParsed = parseInt(currentValue as string, 10) - pastValueParsed;

  let evolution: number;

  if (currentValueParsed === 0 || Number.isNaN(currentValueParsed)) {
    evolution = 0;
  } else if (pastValueParsed === 0 || Number.isNaN(pastValueParsed)) {
    evolution = 100;
  } else {
    evolution = (currentValueParsed / pastValueParsed) * 100;
  }

  return evolution;
}

function formatEvolution(evolution: number) {
  return `${evolution > 0 ? Matomo.numbers.symbolPlus : ''}${Math.round(evolution)}}%`;
}

export default function getFormattedEvolution(
  currentValue: string|number,
  pastValue: string|number,
): string {
  const evolution = calculateEvolution(currentValue, pastValue);
  return formatEvolution(evolution);
}

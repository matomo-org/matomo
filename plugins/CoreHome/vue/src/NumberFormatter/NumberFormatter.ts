/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Matomo from '../Matomo/Matomo';

const { $ } = window;

class NumberFormatter {
  defaultMinFractionDigits = 0;

  defaultMaxFractionDigits = 2;

  private format(
    val: string|number,
    formatPattern: string,
    maxFractionDigits: number,
    minFractionDigits: number,
  ): string {
    if (!$.isNumeric(val)) {
      return String(val);
    }

    let value = (val as number);

    let pattern = formatPattern || Matomo.numbers.patternNumber;

    const patterns = pattern.split(';');
    if (patterns.length === 1) {
      // No explicit negative pattern was provided, construct it.
      patterns.push(`-${patterns[0]}`);
    }

    // Ensure that the value is positive and has the right number of digits.
    const negative = value < 0;
    pattern = negative ? patterns[1] : patterns[0];

    value = Math.abs(value);

    // round value to maximal number of fraction digits
    if (maxFractionDigits >= 0) {
      const factionFactor = 10 ** maxFractionDigits;
      value = Math.round(value * factionFactor) / factionFactor;
    }

    // Split the number into major and minor digits.
    const valueParts = value.toString().split('.');
    let majorDigits = valueParts[0];

    // Account for maxFractionDigits = 0, where the number won't
    // have a decimal point, and $valueParts[1] won't be set.
    let minorDigits = valueParts[1] || '';

    const usesGrouping = (pattern.indexOf(',') !== -1);

    // if pattern has number groups, parse them.
    if (usesGrouping) {
      const primaryGroupMatches = pattern.match(/#+0/);
      const primaryGroupSize = primaryGroupMatches?.[0].length || 0;
      let secondaryGroupSize = primaryGroupMatches?.[0].length || 0;
      const numberGroups = pattern.split(',');

      // check for distinct secondary group size.
      if (numberGroups.length > 2) {
        secondaryGroupSize = numberGroups[1].length;
      }

      // Reverse the major digits, since they are grouped from the right.
      const digits = majorDigits.split('').reverse();
      // Group the major digits.
      let groups = [];

      groups.push(digits.splice(0, primaryGroupSize).reverse().join(''));

      while (digits.length) {
        groups.push(digits.splice(0, secondaryGroupSize).reverse().join(''));
      }

      // Reverse the groups and the digits inside of them.
      groups = groups.reverse();
      // Reconstruct the major digits.
      majorDigits = groups.join(',');
    }

    if (minFractionDigits > 0) {
      // Strip any trailing zeroes.
      minorDigits = minorDigits.replace(/0+$/, '');
      if (
        minorDigits.length < minFractionDigits
        && minorDigits.length < maxFractionDigits
      ) {
        // Now there are too few digits, re-add trailing zeroes
        // until the desired length is reached.
        const neededZeroes = minFractionDigits - minorDigits.length;
        minorDigits += (new Array(neededZeroes + 1)).join('0');
      }
    }

    // Assemble the final number and insert it into the pattern.
    let result = minorDigits ? `${majorDigits}.${minorDigits}` : majorDigits;
    result = pattern.replace(/#(?:[.,]#+)*0(?:[,.][0#]+)*/, result);

    // Localize the number.
    return this.replaceSymbols(result);
  }

  private replaceSymbols(value: string): string {
    const replacements = {
      '.': Matomo.numbers.symbolDecimal,
      ',': Matomo.numbers.symbolGroup,
      '+': Matomo.numbers.symbolPlus,
      '-': Matomo.numbers.symbolMinus,
      '%': Matomo.numbers.symbolPercent,
    };

    let newValue = '';
    const valueParts = value.split('');

    valueParts.forEach((val) => {
      let valueReplaced = val;

      Object.entries(replacements).some(([char, replacement]) => {
        if (valueReplaced.indexOf(char) !== -1) {
          valueReplaced = valueReplaced.replace(char, replacement);
          return true;
        }

        return false;
      });

      newValue += valueReplaced;
    });

    return newValue;
  }

  private valOrDefault(val: number|undefined, def: number): number {
    if (typeof val === 'undefined') {
      return def;
    }

    return val;
  }

  private getMaxFractionDigitsForCompactFormat(valueLength: number) {
    return valueLength === 1 ? 1 : 0;
  }

  private determineCorrectCompactPattern(
    patterns: Record<string, string>,
    value: number,
  ): [string, number] {
    let factor = 0;
    let finalFactor = 0;
    let patternId = '';

    if (Math.round(value) < 1000) {
      return ['0', 1];
    }

    for (factor = 1000; factor <= 10000000000000000000; factor *= 10) {
      const patternOne = `${factor}One`;
      const patternOther = `${factor}Other`;

      if (
        Math.round(value / factor) === 1
        && patterns?.[patternOne] !== ''
      ) {
        finalFactor = factor;
        patternId = patternOne;
      } else if (
        Math.round(value / factor) >= 1
        && patterns?.[patternOther] !== ''
      ) {
        finalFactor = factor;
        patternId = patternOther;
      }

      if (patterns?.[patternId]) {
        const charCount = patterns?.[patternId].match(/0/g)?.length || 1;

        if (Math.round((value * 10 ** charCount) / (factor * 10)) < 10 ** charCount) {
          break;
        }
      }
    }

    return [patterns?.[patternId] as string || '0', finalFactor];
  }

  private formatCompact(pattern: string, factor: number, value: number) {
    const charCount = pattern.match(/0/g)?.length || 0;
    let finalFactor = factor;

    if (charCount > 1) {
      finalFactor /= 10 ** (charCount - 1);
    }

    const maximumFractionDigits = this.getMaxFractionDigitsForCompactFormat(charCount);

    // cut off numbers after a certain decimal, as formatNumber would round otherwise
    const digitCountFactor = 10 ** maximumFractionDigits;
    const finalValue = Math.round((value / finalFactor) * digitCountFactor) / digitCountFactor;

    const formattedNumber = this.formatNumber(finalValue, maximumFractionDigits, 0);

    return pattern.replace(/(0+)/, formattedNumber).replace(/('\.')/, '.');
  }

  public parseFormattedNumber(value: string): number {
    const isNegative = value.indexOf(Matomo.numbers.symbolMinus) > -1 || value.startsWith('-');
    const numberParts = value.split(Matomo.numbers.symbolDecimal);

    numberParts.forEach((val, index) => {
      numberParts[index] = val.replace(/[^0-9]/g, '');
    });

    return (isNegative ? -1 : 1) * parseFloat(numberParts.join('.'));
  }

  public formatNumber(
    value: string|number,
    maxFractionDigits?: number,
    minFractionDigits?: number,
  ): string {
    return this.format(
      value,
      Matomo.numbers.patternNumber,
      this.valOrDefault(maxFractionDigits, this.defaultMaxFractionDigits),
      this.valOrDefault(minFractionDigits, this.defaultMinFractionDigits),
    );
  }

  public formatPercent(
    value: string|number,
    maxFractionDigits?: number,
    minFractionDigits?: number,
  ): string {
    return this.format(
      value,
      Matomo.numbers.patternPercent,
      this.valOrDefault(maxFractionDigits, this.defaultMaxFractionDigits),
      this.valOrDefault(minFractionDigits, this.defaultMinFractionDigits),
    );
  }

  public formatCurrency(
    value: string|number,
    currency: string,
    maxFractionDigits?: number,
    minFractionDigits?: number,
  ): string {
    const formatted = this.format(
      value,
      Matomo.numbers.patternCurrency,
      this.valOrDefault(maxFractionDigits, this.defaultMaxFractionDigits),
      this.valOrDefault(minFractionDigits, this.defaultMinFractionDigits),
    );
    return formatted.replace('¤', currency);
  }

  public formatNumberCompact(value: string|number) {
    const val = (value as number);
    const [compactPattern, factor] = this.determineCorrectCompactPattern(
      Matomo.numbers.patternsCompactNumber || [],
      val,
    );

    // In case no special formatting should be used, we use the default number format
    if (Math.round(val) < 1000 || compactPattern === '0') {
      return this.formatNumber(
        val,
        this.getMaxFractionDigitsForCompactFormat(Math.round(val)),
        0,
      );
    }

    return this.formatCompact(compactPattern, factor, val);
  }

  public formatCurrencyCompact(value: string|number, currency: string) {
    const val = (value as number);
    const [compactPattern, factor] = this.determineCorrectCompactPattern(
      Matomo.numbers.patternsCompactCurrency || [],
      val,
    );

    // In case no special formatting should be used, we use the default number format
    if (Math.round(val) < 1000 || compactPattern === '0') {
      return this.formatCurrency(
        val,
        currency,
        this.getMaxFractionDigitsForCompactFormat(Math.round(val)),
        0,
      );
    }

    return this.formatCompact(compactPattern, factor, val).replace('¤', currency);
  }

  public formatEvolution(
    evolution: string|number,
    maxFractionDigits?: number,
    minFractionDigits?: number,
    noSign?: boolean,
  ): string {
    if (noSign) {
      return this.formatPercent(
        Math.abs(evolution as number),
        maxFractionDigits,
        minFractionDigits,
      );
    }
    const formattedEvolution = this.formatPercent(evolution, maxFractionDigits, minFractionDigits);
    return `${evolution as number > 0 ? Matomo.numbers.symbolPlus : ''}${formattedEvolution}`;
  }

  public calculateAndFormatEvolution(
    currentValue: string|number,
    pastValue: string|number,
    noSign?: boolean,
  ) {
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

    let maxFractionDigits = 3;

    if (Math.abs(evolution) > 100) {
      maxFractionDigits = 0;
    } else if (Math.abs(evolution) > 10) {
      maxFractionDigits = 1;
    } else if (Math.abs(evolution) > 1) {
      maxFractionDigits = 2;
    }

    return this.formatEvolution(evolution, maxFractionDigits, 0, noSign);
  }
}

export default new NumberFormatter();

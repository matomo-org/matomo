/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 *  Number Formatter for formatting numbers, percent and currencies values
 *
 * @type {object}
 */
var NumberFormatter = (function () {

    var minimumFractionDigits = 0;
    var maximumFractionDigits = 2;

    /**
     * Formats the given numeric value with the given pattern
     *
     * @param value
     * @param pattern
     * @returns {string}
     */
    function format(value, pattern) {

        if (!$.isNumeric(value)) {
            return value;
        }
        pattern = pattern || piwik.numbers.patternNumber;

        var patterns = pattern.split(';');
        if (patterns.length == 1) {
            // No explicit negative pattern was provided, construct it.
            patterns.push('-' + patterns[0])
        }

        // Ensure that the value is positive and has the right number of digits.
        var negative = value < 0;
        pattern = negative ? patterns[1] : patterns[0];

        var usesGrouping = (pattern.indexOf(',') != -1);
        // if pattern has number groups, parse them.
        if (usesGrouping) {
            var primaryGroupMatches = pattern.match(/#+0/);
            var primaryGroupSize = primaryGroupMatches[0].length;
            var secondaryGroupSize = primaryGroupMatches[0].length;
            var numberGroups = pattern.split(',');
            // check for distinct secondary group size.
            if (numberGroups.length > 2) {
                secondaryGroupSize = numberGroups[1].length;
            }
        }

        var signMultiplier = negative ? '-1' : '1';
        value = value * signMultiplier;
        // Split the number into major and minor digits.
        var valueParts = value.toString().split('.');
        var majorDigits = valueParts[0];
        // Account for maximumFractionDigits = 0, where the number won't
        // have a decimal point, and $valueParts[1] won't be set.
        minorDigits = valueParts[1] || '';
        if (usesGrouping) {
            // Reverse the major digits, since they are grouped from the right.
            majorDigits = majorDigits.split('').reverse();
            // Group the major digits.
            var groups = [];
            groups.push(majorDigits.splice(0, primaryGroupSize).reverse().join(''));
            while (majorDigits.length) {
                groups.push(majorDigits.splice(0, secondaryGroupSize).reverse().join(''));
            }
            // Reverse the groups and the digits inside of them.
            groups = groups.reverse();
            // Reconstruct the major digits.
            majorDigits = groups.join(',');
        }
        if (minimumFractionDigits < maximumFractionDigits) {
            // Strip any trailing zeroes.
            var minorDigits = minorDigits.replace(/0+$/,'');
            if (minorDigits.length < minimumFractionDigits) {
                // Now there are too few digits, re-add trailing zeroes
                // until the desired length is reached.
                var neededZeroes = minimumFractionDigits - minorDigits.length;
                minorDigits += (new Array(neededZeroes+1)).join('0');
            }
        }
        // Assemble the final number and insert it into the pattern.
        value = minorDigits ? majorDigits + '.' + minorDigits : majorDigits;
        value = pattern.replace(/#(?:[\.,]#+)*0(?:[,\.][0#]+)*/, value);
        // Localize the number.
        return replaceSymbols(value);
    }

    /**
     * Replaces the placeholders with real symbols
     *
     * @param value
     * @returns {string}
     */
    function replaceSymbols(value) {
        var replacements = {
            '.': piwik.numbers.symbolDecimal,
            ',': piwik.numbers.symbolGroup,
            '+': piwik.numbers.symbolPlus,
            '-': piwik.numbers.symbolMinus,
            '%': piwik.numbers.symbolPercent
        };

        var newValue = '';
        var valueParts = value.split('');

        $.each(valueParts, function(index, value) {
            $.each(replacements, function(char, replacement) {
                if (value.indexOf(char) != -1) {
                    value = value.replace(char, replacement);
                    return false;
                }
            });
            newValue += value;
        });

        return newValue;
    }

    /**
     * Public available methods
     */
    return {

        formatNumber: function (value) {
            return format(value, piwik.numbers.patternNumber);
        },

        formatPercent: function (value) {
            return format(value, piwik.numbers.patternPercent);
        },

        formatCurrency: function (value, currency) {
            var formatted = format(value, piwik.numbers.patternCurrency);
            return formatted.replace('¤', currency);
        }
    }
})();

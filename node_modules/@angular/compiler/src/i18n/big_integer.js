/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/compiler/src/i18n/big_integer", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.BigIntExponentiation = exports.BigIntForMultiplication = exports.BigInteger = void 0;
    /**
     * Represents a big integer using a buffer of its individual digits, with the least significant
     * digit stored at the beginning of the array (little endian).
     *
     * For performance reasons, each instance is mutable. The addition operation can be done in-place
     * to reduce memory pressure of allocation for the digits array.
     */
    var BigInteger = /** @class */ (function () {
        /**
         * Creates a big integer using its individual digits in little endian storage.
         */
        function BigInteger(digits) {
            this.digits = digits;
        }
        BigInteger.zero = function () {
            return new BigInteger([0]);
        };
        BigInteger.one = function () {
            return new BigInteger([1]);
        };
        /**
         * Creates a clone of this instance.
         */
        BigInteger.prototype.clone = function () {
            return new BigInteger(this.digits.slice());
        };
        /**
         * Returns a new big integer with the sum of `this` and `other` as its value. This does not mutate
         * `this` but instead returns a new instance, unlike `addToSelf`.
         */
        BigInteger.prototype.add = function (other) {
            var result = this.clone();
            result.addToSelf(other);
            return result;
        };
        /**
         * Adds `other` to the instance itself, thereby mutating its value.
         */
        BigInteger.prototype.addToSelf = function (other) {
            var maxNrOfDigits = Math.max(this.digits.length, other.digits.length);
            var carry = 0;
            for (var i = 0; i < maxNrOfDigits; i++) {
                var digitSum = carry;
                if (i < this.digits.length) {
                    digitSum += this.digits[i];
                }
                if (i < other.digits.length) {
                    digitSum += other.digits[i];
                }
                if (digitSum >= 10) {
                    this.digits[i] = digitSum - 10;
                    carry = 1;
                }
                else {
                    this.digits[i] = digitSum;
                    carry = 0;
                }
            }
            // Apply a remaining carry if needed.
            if (carry > 0) {
                this.digits[maxNrOfDigits] = 1;
            }
        };
        /**
         * Builds the decimal string representation of the big integer. As this is stored in
         * little endian, the digits are concatenated in reverse order.
         */
        BigInteger.prototype.toString = function () {
            var res = '';
            for (var i = this.digits.length - 1; i >= 0; i--) {
                res += this.digits[i];
            }
            return res;
        };
        return BigInteger;
    }());
    exports.BigInteger = BigInteger;
    /**
     * Represents a big integer which is optimized for multiplication operations, as its power-of-twos
     * are memoized. See `multiplyBy()` for details on the multiplication algorithm.
     */
    var BigIntForMultiplication = /** @class */ (function () {
        function BigIntForMultiplication(value) {
            this.powerOfTwos = [value];
        }
        /**
         * Returns the big integer itself.
         */
        BigIntForMultiplication.prototype.getValue = function () {
            return this.powerOfTwos[0];
        };
        /**
         * Computes the value for `num * b`, where `num` is a JS number and `b` is a big integer. The
         * value for `b` is represented by a storage model that is optimized for this computation.
         *
         * This operation is implemented in N(log2(num)) by continuous halving of the number, where the
         * least-significant bit (LSB) is tested in each iteration. If the bit is set, the bit's index is
         * used as exponent into the power-of-two multiplication of `b`.
         *
         * As an example, consider the multiplication num=42, b=1337. In binary 42 is 0b00101010 and the
         * algorithm unrolls into the following iterations:
         *
         *  Iteration | num        | LSB  | b * 2^iter | Add? | product
         * -----------|------------|------|------------|------|--------
         *  0         | 0b00101010 | 0    | 1337       | No   | 0
         *  1         | 0b00010101 | 1    | 2674       | Yes  | 2674
         *  2         | 0b00001010 | 0    | 5348       | No   | 2674
         *  3         | 0b00000101 | 1    | 10696      | Yes  | 13370
         *  4         | 0b00000010 | 0    | 21392      | No   | 13370
         *  5         | 0b00000001 | 1    | 42784      | Yes  | 56154
         *  6         | 0b00000000 | 0    | 85568      | No   | 56154
         *
         * The computed product of 56154 is indeed the correct result.
         *
         * The `BigIntForMultiplication` representation for a big integer provides memoized access to the
         * power-of-two values to reduce the workload in computing those values.
         */
        BigIntForMultiplication.prototype.multiplyBy = function (num) {
            var product = BigInteger.zero();
            this.multiplyByAndAddTo(num, product);
            return product;
        };
        /**
         * See `multiplyBy()` for details. This function allows for the computed product to be added
         * directly to the provided result big integer.
         */
        BigIntForMultiplication.prototype.multiplyByAndAddTo = function (num, result) {
            for (var exponent = 0; num !== 0; num = num >>> 1, exponent++) {
                if (num & 1) {
                    var value = this.getMultipliedByPowerOfTwo(exponent);
                    result.addToSelf(value);
                }
            }
        };
        /**
         * Computes and memoizes the big integer value for `this.number * 2^exponent`.
         */
        BigIntForMultiplication.prototype.getMultipliedByPowerOfTwo = function (exponent) {
            // Compute the powers up until the requested exponent, where each value is computed from its
            // predecessor. This is simple as `this.number * 2^(exponent - 1)` only has to be doubled (i.e.
            // added to itself) to reach `this.number * 2^exponent`.
            for (var i = this.powerOfTwos.length; i <= exponent; i++) {
                var previousPower = this.powerOfTwos[i - 1];
                this.powerOfTwos[i] = previousPower.add(previousPower);
            }
            return this.powerOfTwos[exponent];
        };
        return BigIntForMultiplication;
    }());
    exports.BigIntForMultiplication = BigIntForMultiplication;
    /**
     * Represents an exponentiation operation for the provided base, of which exponents are computed and
     * memoized. The results are represented by a `BigIntForMultiplication` which is tailored for
     * multiplication operations by memoizing the power-of-twos. This effectively results in a matrix
     * representation that is lazily computed upon request.
     */
    var BigIntExponentiation = /** @class */ (function () {
        function BigIntExponentiation(base) {
            this.base = base;
            this.exponents = [new BigIntForMultiplication(BigInteger.one())];
        }
        /**
         * Compute the value for `this.base^exponent`, resulting in a big integer that is optimized for
         * further multiplication operations.
         */
        BigIntExponentiation.prototype.toThePowerOf = function (exponent) {
            // Compute the results up until the requested exponent, where every value is computed from its
            // predecessor. This is because `this.base^(exponent - 1)` only has to be multiplied by `base`
            // to reach `this.base^exponent`.
            for (var i = this.exponents.length; i <= exponent; i++) {
                var value = this.exponents[i - 1].multiplyBy(this.base);
                this.exponents[i] = new BigIntForMultiplication(value);
            }
            return this.exponents[exponent];
        };
        return BigIntExponentiation;
    }());
    exports.BigIntExponentiation = BigIntExponentiation;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYmlnX2ludGVnZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvaTE4bi9iaWdfaW50ZWdlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSDs7Ozs7O09BTUc7SUFDSDtRQVNFOztXQUVHO1FBQ0gsb0JBQXFDLE1BQWdCO1lBQWhCLFdBQU0sR0FBTixNQUFNLENBQVU7UUFBRyxDQUFDO1FBWGxELGVBQUksR0FBWDtZQUNFLE9BQU8sSUFBSSxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQzdCLENBQUM7UUFFTSxjQUFHLEdBQVY7WUFDRSxPQUFPLElBQUksVUFBVSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQUM3QixDQUFDO1FBT0Q7O1dBRUc7UUFDSCwwQkFBSyxHQUFMO1lBQ0UsT0FBTyxJQUFJLFVBQVUsQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssRUFBRSxDQUFDLENBQUM7UUFDN0MsQ0FBQztRQUVEOzs7V0FHRztRQUNILHdCQUFHLEdBQUgsVUFBSSxLQUFpQjtZQUNuQixJQUFNLE1BQU0sR0FBRyxJQUFJLENBQUMsS0FBSyxFQUFFLENBQUM7WUFDNUIsTUFBTSxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsQ0FBQztZQUN4QixPQUFPLE1BQU0sQ0FBQztRQUNoQixDQUFDO1FBRUQ7O1dBRUc7UUFDSCw4QkFBUyxHQUFULFVBQVUsS0FBaUI7WUFDekIsSUFBTSxhQUFhLEdBQUcsSUFBSSxDQUFDLEdBQUcsQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sRUFBRSxLQUFLLENBQUMsTUFBTSxDQUFDLE1BQU0sQ0FBQyxDQUFDO1lBQ3hFLElBQUksS0FBSyxHQUFHLENBQUMsQ0FBQztZQUNkLEtBQUssSUFBSSxDQUFDLEdBQUcsQ0FBQyxFQUFFLENBQUMsR0FBRyxh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QyxDQUFDLENBQUM7Z0JBQzlDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDLEdBQUcsYUFBYSxDQUFDLEdBQUcsQ0FBQyxhQUFhLENBQUMsQ0FBQzthQUN4RDtZQUNELE9BQU8sSUFBSSxDQUFDLFdBQVcsQ0FBQyxRQUFRLENBQUMsQ0FBQztRQUNwQyxDQUFDO1FBQ0gsOEJBQUM7SUFBRCxDQUFDLEFBM0VELElBMkVDO0lBM0VZLDBEQUF1QjtJQTZFcEM7Ozs7O09BS0c7SUFDSDtRQUdFLDhCQUE2QixJQUFZO1lBQVosU0FBSSxHQUFKLElBQUksQ0FBUTtZQUZ4QixjQUFTLEdBQUcsQ0FBQyxJQUFJLHVCQUF1QixDQUFDLFVBQVUsQ0FBQyxHQUFHLEVBQUUsQ0FBQyxDQUFDLENBQUM7UUFFakMsQ0FBQztRQUU3Qzs7O1dBR0c7UUFDSCwyQ0FBWSxHQUFaLFVBQWEsUUFBZ0I7WUFDM0IsOEZBQThGO1lBQzlGLDhGQUE4RjtZQUM5RixpQ0FBaUM7WUFDakMsS0FBSyxJQUFJLENBQUMsR0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBRSxDQUFDLElBQUksUUFBUSxFQUFFLENBQUMsRUFBRSxFQUFFO2dCQUN0RCxJQUFNLEtBQUssR0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDO2dCQUMxRCxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxHQUFHLElBQUksdUJBQXVCLENBQUMsS0FBSyxDQUFDLENBQUM7YUFDeEQ7WUFDRCxPQUFPLElBQUksQ0FBQyxTQUFTLENBQUMsUUFBUSxDQUFDLENBQUM7UUFDbEMsQ0FBQztRQUNILDJCQUFDO0lBQUQsQ0FBQyxBQW5CRCxJQW1CQztJQW5CWSxvREFBb0IiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuLyoqXG4gKiBSZXByZXNlbnRzIGEgYmlnIGludGVnZXIgdXNpbmcgYSBidWZmZXIgb2YgaXRzIGluZGl2aWR1YWwgZGlnaXRzLCB3aXRoIHRoZSBsZWFzdCBzaWduaWZpY2FudFxuICogZGlnaXQgc3RvcmVkIGF0IHRoZSBiZWdpbm5pbmcgb2YgdGhlIGFycmF5IChsaXR0bGUgZW5kaWFuKS5cbiAqXG4gKiBGb3IgcGVyZm9ybWFuY2UgcmVhc29ucywgZWFjaCBpbnN0YW5jZSBpcyBtdXRhYmxlLiBUaGUgYWRkaXRpb24gb3BlcmF0aW9uIGNhbiBiZSBkb25lIGluLXBsYWNlXG4gKiB0byByZWR1Y2UgbWVtb3J5IHByZXNzdXJlIG9mIGFsbG9jYXRpb24gZm9yIHRoZSBkaWdpdHMgYXJyYXkuXG4gKi9cbmV4cG9ydCBjbGFzcyBCaWdJbnRlZ2VyIHtcbiAgc3RhdGljIHplcm8oKTogQmlnSW50ZWdlciB7XG4gICAgcmV0dXJuIG5ldyBCaWdJbnRlZ2VyKFswXSk7XG4gIH1cblxuICBzdGF0aWMgb25lKCk6IEJpZ0ludGVnZXIge1xuICAgIHJldHVybiBuZXcgQmlnSW50ZWdlcihbMV0pO1xuICB9XG5cbiAgLyoqXG4gICAqIENyZWF0ZXMgYSBiaWcgaW50ZWdlciB1c2luZyBpdHMgaW5kaXZpZHVhbCBkaWdpdHMgaW4gbGl0dGxlIGVuZGlhbiBzdG9yYWdlLlxuICAgKi9cbiAgcHJpdmF0ZSBjb25zdHJ1Y3Rvcihwcml2YXRlIHJlYWRvbmx5IGRpZ2l0czogbnVtYmVyW10pIHt9XG5cbiAgLyoqXG4gICAqIENyZWF0ZXMgYSBjbG9uZSBvZiB0aGlzIGluc3RhbmNlLlxuICAgKi9cbiAgY2xvbmUoKTogQmlnSW50ZWdlciB7XG4gICAgcmV0dXJuIG5ldyBCaWdJbnRlZ2VyKHRoaXMuZGlnaXRzLnNsaWNlKCkpO1xuICB9XG5cbiAgLyoqXG4gICAqIFJldHVybnMgYSBuZXcgYmlnIGludGVnZXIgd2l0aCB0aGUgc3VtIG9mIGB0aGlzYCBhbmQgYG90aGVyYCBhcyBpdHMgdmFsdWUuIFRoaXMgZG9lcyBub3QgbXV0YXRlXG4gICAqIGB0aGlzYCBidXQgaW5zdGVhZCByZXR1cm5zIGEgbmV3IGluc3RhbmNlLCB1bmxpa2UgYGFkZFRvU2VsZmAuXG4gICAqL1xuICBhZGQob3RoZXI6IEJpZ0ludGVnZXIpOiBCaWdJbnRlZ2VyIHtcbiAgICBjb25zdCByZXN1bHQgPSB0aGlzLmNsb25lKCk7XG4gICAgcmVzdWx0LmFkZFRvU2VsZihvdGhlcik7XG4gICAgcmV0dXJuIHJlc3VsdDtcbiAgfVxuXG4gIC8qKlxuICAgKiBBZGRzIGBvdGhlcmAgdG8gdGhlIGluc3RhbmNlIGl0c2VsZiwgdGhlcmVieSBtdXRhdGluZyBpdHMgdmFsdWUuXG4gICAqL1xuICBhZGRUb1NlbGYob3RoZXI6IEJpZ0ludGVnZXIpOiB2b2lkIHtcbiAgICBjb25zdCBtYXhOck9mRGlnaXRzID0gTWF0aC5tYXgodGhpcy5kaWdpdHMubGVuZ3RoLCBvdGhlci5kaWdpdHMubGVuZ3RoKTtcbiAgICBsZXQgY2FycnkgPSAwO1xuICAgIGZvciAobGV0IGkgPSAwOyBpIDwgbWF4TnJPZkRpZ2l0czsgaSsrKSB7XG4gICAgICBsZXQgZGlnaXRTdW0gPSBjYXJyeTtcbiAgICAgIGlmIChpIDwgdGhpcy5kaWdpdHMubGVuZ3RoKSB7XG4gICAgICAgIGRpZ2l0U3VtICs9IHRoaXMuZGlnaXRzW2ldO1xuICAgICAgfVxuICAgICAgaWYgKGkgPCBvdGhlci5kaWdpdHMubGVuZ3RoKSB7XG4gICAgICAgIGRpZ2l0U3VtICs9IG90aGVyLmRpZ2l0c1tpXTtcbiAgICAgIH1cblxuICAgICAgaWYgKGRpZ2l0U3VtID49IDEwKSB7XG4gICAgICAgIHRoaXMuZGlnaXRzW2ldID0gZGlnaXRTdW0gLSAxMDtcbiAgICAgICAgY2FycnkgPSAxO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgdGhpcy5kaWdpdHNbaV0gPSBkaWdpdFN1bTtcbiAgICAgICAgY2FycnkgPSAwO1xuICAgICAgfVxuICAgIH1cblxuICAgIC8vIEFwcGx5IGEgcmVtYWluaW5nIGNhcnJ5IGlmIG5lZWRlZC5cbiAgICBpZiAoY2FycnkgPiAwKSB7XG4gICAgICB0aGlzLmRpZ2l0c1ttYXhOck9mRGlnaXRzXSA9IDE7XG4gICAgfVxuICB9XG5cbiAgLyoqXG4gICAqIEJ1aWxkcyB0aGUgZGVjaW1hbCBzdHJpbmcgcmVwcmVzZW50YXRpb24gb2YgdGhlIGJpZyBpbnRlZ2VyLiBBcyB0aGlzIGlzIHN0b3JlZCBpblxuICAgKiBsaXR0bGUgZW5kaWFuLCB0aGUgZGlnaXRzIGFyZSBjb25jYXRlbmF0ZWQgaW4gcmV2ZXJzZSBvcmRlci5cbiAgICovXG4gIHRvU3RyaW5nKCk6IHN0cmluZyB7XG4gICAgbGV0IHJlcyA9ICcnO1xuICAgIGZvciAobGV0IGkgPSB0aGlzLmRpZ2l0cy5sZW5ndGggLSAxOyBpID49IDA7IGktLSkge1xuICAgICAgcmVzICs9IHRoaXMuZGlnaXRzW2ldO1xuICAgIH1cbiAgICByZXR1cm4gcmVzO1xuICB9XG59XG5cbi8qKlxuICogUmVwcmVzZW50cyBhIGJpZyBpbnRlZ2VyIHdoaWNoIGlzIG9wdGltaXplZCBmb3IgbXVsdGlwbGljYXRpb24gb3BlcmF0aW9ucywgYXMgaXRzIHBvd2VyLW9mLXR3b3NcbiAqIGFyZSBtZW1vaXplZC4gU2VlIGBtdWx0aXBseUJ5KClgIGZvciBkZXRhaWxzIG9uIHRoZSBtdWx0aXBsaWNhdGlvbiBhbGdvcml0aG0uXG4gKi9cbmV4cG9ydCBjbGFzcyBCaWdJbnRGb3JNdWx0aXBsaWNhdGlvbiB7XG4gIC8qKlxuICAgKiBTdG9yZXMgYWxsIG1lbW9pemVkIHBvd2VyLW9mLXR3b3MsIHdoZXJlIGVhY2ggaW5kZXggcmVwcmVzZW50cyBgdGhpcy5udW1iZXIgKiAyXmluZGV4YC5cbiAgICovXG4gIHByaXZhdGUgcmVhZG9ubHkgcG93ZXJPZlR3b3M6IEJpZ0ludGVnZXJbXTtcblxuICBjb25zdHJ1Y3Rvcih2YWx1ZTogQmlnSW50ZWdlcikge1xuICAgIHRoaXMucG93ZXJPZlR3b3MgPSBbdmFsdWVdO1xuICB9XG5cbiAgLyoqXG4gICAqIFJldHVybnMgdGhlIGJpZyBpbnRlZ2VyIGl0c2VsZi5cbiAgICovXG4gIGdldFZhbHVlKCk6IEJpZ0ludGVnZXIge1xuICAgIHJldHVybiB0aGlzLnBvd2VyT2ZUd29zWzBdO1xuICB9XG5cbiAgLyoqXG4gICAqIENvbXB1dGVzIHRoZSB2YWx1ZSBmb3IgYG51bSAqIGJgLCB3aGVyZSBgbnVtYCBpcyBhIEpTIG51bWJlciBhbmQgYGJgIGlzIGEgYmlnIGludGVnZXIuIFRoZVxuICAgKiB2YWx1ZSBmb3IgYGJgIGlzIHJlcHJlc2VudGVkIGJ5IGEgc3RvcmFnZSBtb2RlbCB0aGF0IGlzIG9wdGltaXplZCBmb3IgdGhpcyBjb21wdXRhdGlvbi5cbiAgICpcbiAgICogVGhpcyBvcGVyYXRpb24gaXMgaW1wbGVtZW50ZWQgaW4gTihsb2cyKG51bSkpIGJ5IGNvbnRpbnVvdXMgaGFsdmluZyBvZiB0aGUgbnVtYmVyLCB3aGVyZSB0aGVcbiAgICogbGVhc3Qtc2lnbmlmaWNhbnQgYml0IChMU0IpIGlzIHRlc3RlZCBpbiBlYWNoIGl0ZXJhdGlvbi4gSWYgdGhlIGJpdCBpcyBzZXQsIHRoZSBiaXQncyBpbmRleCBpc1xuICAgKiB1c2VkIGFzIGV4cG9uZW50IGludG8gdGhlIHBvd2VyLW9mLXR3byBtdWx0aXBsaWNhdGlvbiBvZiBgYmAuXG4gICAqXG4gICAqIEFzIGFuIGV4YW1wbGUsIGNvbnNpZGVyIHRoZSBtdWx0aXBsaWNhdGlvbiBudW09NDIsIGI9MTMzNy4gSW4gYmluYXJ5IDQyIGlzIDBiMDAxMDEwMTAgYW5kIHRoZVxuICAgKiBhbGdvcml0aG0gdW5yb2xscyBpbnRvIHRoZSBmb2xsb3dpbmcgaXRlcmF0aW9uczpcbiAgICpcbiAgICogIEl0ZXJhdGlvbiB8IG51bSAgICAgICAgfCBMU0IgIHwgYiAqIDJeaXRlciB8IEFkZD8gfCBwcm9kdWN0XG4gICAqIC0tLS0tLS0tLS0tfC0tLS0tLS0tLS0tLXwtLS0tLS18LS0tLS0tLS0tLS0tfC0tLS0tLXwtLS0tLS0tLVxuICAgKiAgMCAgICAgICAgIHwgMGIwMDEwMTAxMCB8IDAgICAgfCAxMzM3ICAgICAgIHwgTm8gICB8IDBcbiAgICogIDEgICAgICAgICB8IDBiMDAwMTAxMDEgfCAxICAgIHwgMjY3NCAgICAgICB8IFllcyAgfCAyNjc0XG4gICAqICAyICAgICAgICAgfCAwYjAwMDAxMDEwIHwgMCAgICB8IDUzNDggICAgICAgfCBObyAgIHwgMjY3NFxuICAgKiAgMyAgICAgICAgIHwgMGIwMDAwMDEwMSB8IDEgICAgfCAxMDY5NiAgICAgIHwgWWVzICB8IDEzMzcwXG4gICAqICA0ICAgICAgICAgfCAwYjAwMDAwMDEwIHwgMCAgICB8IDIxMzkyICAgICAgfCBObyAgIHwgMTMzNzBcbiAgICogIDUgICAgICAgICB8IDBiMDAwMDAwMDEgfCAxICAgIHwgNDI3ODQgICAgICB8IFllcyAgfCA1NjE1NFxuICAgKiAgNiAgICAgICAgIHwgMGIwMDAwMDAwMCB8IDAgICAgfCA4NTU2OCAgICAgIHwgTm8gICB8IDU2MTU0XG4gICAqXG4gICAqIFRoZSBjb21wdXRlZCBwcm9kdWN0IG9mIDU2MTU0IGlzIGluZGVlZCB0aGUgY29ycmVjdCByZXN1bHQuXG4gICAqXG4gICAqIFRoZSBgQmlnSW50Rm9yTXVsdGlwbGljYXRpb25gIHJlcHJlc2VudGF0aW9uIGZvciBhIGJpZyBpbnRlZ2VyIHByb3ZpZGVzIG1lbW9pemVkIGFjY2VzcyB0byB0aGVcbiAgICogcG93ZXItb2YtdHdvIHZhbHVlcyB0byByZWR1Y2UgdGhlIHdvcmtsb2FkIGluIGNvbXB1dGluZyB0aG9zZSB2YWx1ZXMuXG4gICAqL1xuICBtdWx0aXBseUJ5KG51bTogbnVtYmVyKTogQmlnSW50ZWdlciB7XG4gICAgY29uc3QgcHJvZHVjdCA9IEJpZ0ludGVnZXIuemVybygpO1xuICAgIHRoaXMubXVsdGlwbHlCeUFuZEFkZFRvKG51bSwgcHJvZHVjdCk7XG4gICAgcmV0dXJuIHByb2R1Y3Q7XG4gIH1cblxuICAvKipcbiAgICogU2VlIGBtdWx0aXBseUJ5KClgIGZvciBkZXRhaWxzLiBUaGlzIGZ1bmN0aW9uIGFsbG93cyBmb3IgdGhlIGNvbXB1dGVkIHByb2R1Y3QgdG8gYmUgYWRkZWRcbiAgICogZGlyZWN0bHkgdG8gdGhlIHByb3ZpZGVkIHJlc3VsdCBiaWcgaW50ZWdlci5cbiAgICovXG4gIG11bHRpcGx5QnlBbmRBZGRUbyhudW06IG51bWJlciwgcmVzdWx0OiBCaWdJbnRlZ2VyKTogdm9pZCB7XG4gICAgZm9yIChsZXQgZXhwb25lbnQgPSAwOyBudW0gIT09IDA7IG51bSA9IG51bSA+Pj4gMSwgZXhwb25lbnQrKykge1xuICAgICAgaWYgKG51bSAmIDEpIHtcbiAgICAgICAgY29uc3QgdmFsdWUgPSB0aGlzLmdldE11bHRpcGxpZWRCeVBvd2VyT2ZUd28oZXhwb25lbnQpO1xuICAgICAgICByZXN1bHQuYWRkVG9TZWxmKHZhbHVlKTtcbiAgICAgIH1cbiAgICB9XG4gIH1cblxuICAvKipcbiAgICogQ29tcHV0ZXMgYW5kIG1lbW9pemVzIHRoZSBiaWcgaW50ZWdlciB2YWx1ZSBmb3IgYHRoaXMubnVtYmVyICogMl5leHBvbmVudGAuXG4gICAqL1xuICBwcml2YXRlIGdldE11bHRpcGxpZWRCeVBvd2VyT2ZUd28oZXhwb25lbnQ6IG51bWJlcik6IEJpZ0ludGVnZXIge1xuICAgIC8vIENvbXB1dGUgdGhlIHBvd2VycyB1cCB1bnRpbCB0aGUgcmVxdWVzdGVkIGV4cG9uZW50LCB3aGVyZSBlYWNoIHZhbHVlIGlzIGNvbXB1dGVkIGZyb20gaXRzXG4gICAgLy8gcHJlZGVjZXNzb3IuIFRoaXMgaXMgc2ltcGxlIGFzIGB0aGlzLm51bWJlciAqIDJeKGV4cG9uZW50IC0gMSlgIG9ubHkgaGFzIHRvIGJlIGRvdWJsZWQgKGkuZS5cbiAgICAvLyBhZGRlZCB0byBpdHNlbGYpIHRvIHJlYWNoIGB0aGlzLm51bWJlciAqIDJeZXhwb25lbnRgLlxuICAgIGZvciAobGV0IGkgPSB0aGlzLnBvd2VyT2ZUd29zLmxlbmd0aDsgaSA8PSBleHBvbmVudDsgaSsrKSB7XG4gICAgICBjb25zdCBwcmV2aW91c1Bvd2VyID0gdGhpcy5wb3dlck9mVHdvc1tpIC0gMV07XG4gICAgICB0aGlzLnBvd2VyT2ZUd29zW2ldID0gcHJldmlvdXNQb3dlci5hZGQocHJldmlvdXNQb3dlcik7XG4gICAgfVxuICAgIHJldHVybiB0aGlzLnBvd2VyT2ZUd29zW2V4cG9uZW50XTtcbiAgfVxufVxuXG4vKipcbiAqIFJlcHJlc2VudHMgYW4gZXhwb25lbnRpYXRpb24gb3BlcmF0aW9uIGZvciB0aGUgcHJvdmlkZWQgYmFzZSwgb2Ygd2hpY2ggZXhwb25lbnRzIGFyZSBjb21wdXRlZCBhbmRcbiAqIG1lbW9pemVkLiBUaGUgcmVzdWx0cyBhcmUgcmVwcmVzZW50ZWQgYnkgYSBgQmlnSW50Rm9yTXVsdGlwbGljYXRpb25gIHdoaWNoIGlzIHRhaWxvcmVkIGZvclxuICogbXVsdGlwbGljYXRpb24gb3BlcmF0aW9ucyBieSBtZW1vaXppbmcgdGhlIHBvd2VyLW9mLXR3b3MuIFRoaXMgZWZmZWN0aXZlbHkgcmVzdWx0cyBpbiBhIG1hdHJpeFxuICogcmVwcmVzZW50YXRpb24gdGhhdCBpcyBsYXppbHkgY29tcHV0ZWQgdXBvbiByZXF1ZXN0LlxuICovXG5leHBvcnQgY2xhc3MgQmlnSW50RXhwb25lbnRpYXRpb24ge1xuICBwcml2YXRlIHJlYWRvbmx5IGV4cG9uZW50cyA9IFtuZXcgQmlnSW50Rm9yTXVsdGlwbGljYXRpb24oQmlnSW50ZWdlci5vbmUoKSldO1xuXG4gIGNvbnN0cnVjdG9yKHByaXZhdGUgcmVhZG9ubHkgYmFzZTogbnVtYmVyKSB7fVxuXG4gIC8qKlxuICAgKiBDb21wdXRlIHRoZSB2YWx1ZSBmb3IgYHRoaXMuYmFzZV5leHBvbmVudGAsIHJlc3VsdGluZyBpbiBhIGJpZyBpbnRlZ2VyIHRoYXQgaXMgb3B0aW1pemVkIGZvclxuICAgKiBmdXJ0aGVyIG11bHRpcGxpY2F0aW9uIG9wZXJhdGlvbnMuXG4gICAqL1xuICB0b1RoZVBvd2VyT2YoZXhwb25lbnQ6IG51bWJlcik6IEJpZ0ludEZvck11bHRpcGxpY2F0aW9uIHtcbiAgICAvLyBDb21wdXRlIHRoZSByZXN1bHRzIHVwIHVudGlsIHRoZSByZXF1ZXN0ZWQgZXhwb25lbnQsIHdoZXJlIGV2ZXJ5IHZhbHVlIGlzIGNvbXB1dGVkIGZyb20gaXRzXG4gICAgLy8gcHJlZGVjZXNzb3IuIFRoaXMgaXMgYmVjYXVzZSBgdGhpcy5iYXNlXihleHBvbmVudCAtIDEpYCBvbmx5IGhhcyB0byBiZSBtdWx0aXBsaWVkIGJ5IGBiYXNlYFxuICAgIC8vIHRvIHJlYWNoIGB0aGlzLmJhc2VeZXhwb25lbnRgLlxuICAgIGZvciAobGV0IGkgPSB0aGlzLmV4cG9uZW50cy5sZW5ndGg7IGkgPD0gZXhwb25lbnQ7IGkrKykge1xuICAgICAgY29uc3QgdmFsdWUgPSB0aGlzLmV4cG9uZW50c1tpIC0gMV0ubXVsdGlwbHlCeSh0aGlzLmJhc2UpO1xuICAgICAgdGhpcy5leHBvbmVudHNbaV0gPSBuZXcgQmlnSW50Rm9yTXVsdGlwbGljYXRpb24odmFsdWUpO1xuICAgIH1cbiAgICByZXR1cm4gdGhpcy5leHBvbmVudHNbZXhwb25lbnRdO1xuICB9XG59XG4iXX0=
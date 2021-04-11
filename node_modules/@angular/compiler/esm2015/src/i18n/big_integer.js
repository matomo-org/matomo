/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * Represents a big integer using a buffer of its individual digits, with the least significant
 * digit stored at the beginning of the array (little endian).
 *
 * For performance reasons, each instance is mutable. The addition operation can be done in-place
 * to reduce memory pressure of allocation for the digits array.
 */
export class BigInteger {
    /**
     * Creates a big integer using its individual digits in little endian storage.
     */
    constructor(digits) {
        this.digits = digits;
    }
    static zero() {
        return new BigInteger([0]);
    }
    static one() {
        return new BigInteger([1]);
    }
    /**
     * Creates a clone of this instance.
     */
    clone() {
        return new BigInteger(this.digits.slice());
    }
    /**
     * Returns a new big integer with the sum of `this` and `other` as its value. This does not mutate
     * `this` but instead returns a new instance, unlike `addToSelf`.
     */
    add(other) {
        const result = this.clone();
        result.addToSelf(other);
        return result;
    }
    /**
     * Adds `other` to the instance itself, thereby mutating its value.
     */
    addToSelf(other) {
        const maxNrOfDigits = Math.max(this.digits.length, other.digits.length);
        let carry = 0;
        for (let i = 0; i < maxNrOfDigits; i++) {
            let digitSum = carry;
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
    }
    /**
     * Builds the decimal string representation of the big integer. As this is stored in
     * little endian, the digits are concatenated in reverse order.
     */
    toString() {
        let res = '';
        for (let i = this.digits.length - 1; i >= 0; i--) {
            res += this.digits[i];
        }
        return res;
    }
}
/**
 * Represents a big integer which is optimized for multiplication operations, as its power-of-twos
 * are memoized. See `multiplyBy()` for details on the multiplication algorithm.
 */
export class BigIntForMultiplication {
    constructor(value) {
        this.powerOfTwos = [value];
    }
    /**
     * Returns the big integer itself.
     */
    getValue() {
        return this.powerOfTwos[0];
    }
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
    multiplyBy(num) {
        const product = BigInteger.zero();
        this.multiplyByAndAddTo(num, product);
        return product;
    }
    /**
     * See `multiplyBy()` for details. This function allows for the computed product to be added
     * directly to the provided result big integer.
     */
    multiplyByAndAddTo(num, result) {
        for (let exponent = 0; num !== 0; num = num >>> 1, exponent++) {
            if (num & 1) {
                const value = this.getMultipliedByPowerOfTwo(exponent);
                result.addToSelf(value);
            }
        }
    }
    /**
     * Computes and memoizes the big integer value for `this.number * 2^exponent`.
     */
    getMultipliedByPowerOfTwo(exponent) {
        // Compute the powers up until the requested exponent, where each value is computed from its
        // predecessor. This is simple as `this.number * 2^(exponent - 1)` only has to be doubled (i.e.
        // added to itself) to reach `this.number * 2^exponent`.
        for (let i = this.powerOfTwos.length; i <= exponent; i++) {
            const previousPower = this.powerOfTwos[i - 1];
            this.powerOfTwos[i] = previousPower.add(previousPower);
        }
        return this.powerOfTwos[exponent];
    }
}
/**
 * Represents an exponentiation operation for the provided base, of which exponents are computed and
 * memoized. The results are represented by a `BigIntForMultiplication` which is tailored for
 * multiplication operations by memoizing the power-of-twos. This effectively results in a matrix
 * representation that is lazily computed upon request.
 */
export class BigIntExponentiation {
    constructor(base) {
        this.base = base;
        this.exponents = [new BigIntForMultiplication(BigInteger.one())];
    }
    /**
     * Compute the value for `this.base^exponent`, resulting in a big integer that is optimized for
     * further multiplication operations.
     */
    toThePowerOf(exponent) {
        // Compute the results up until the requested exponent, where every value is computed from its
        // predecessor. This is because `this.base^(exponent - 1)` only has to be multiplied by `base`
        // to reach `this.base^exponent`.
        for (let i = this.exponents.length; i <= exponent; i++) {
            const value = this.exponents[i - 1].multiplyBy(this.base);
            this.exponents[i] = new BigIntForMultiplication(value);
        }
        return this.exponents[exponent];
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYmlnX2ludGVnZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvaTE4bi9iaWdfaW50ZWdlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFFSDs7Ozs7O0dBTUc7QUFDSCxNQUFNLE9BQU8sVUFBVTtJQVNyQjs7T0FFRztJQUNILFlBQXFDLE1BQWdCO1FBQWhCLFdBQU0sR0FBTixNQUFNLENBQVU7SUFBRyxDQUFDO0lBWHpELE1BQU0sQ0FBQyxJQUFJO1FBQ1QsT0FBTyxJQUFJLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFDN0IsQ0FBQztJQUVELE1BQU0sQ0FBQyxHQUFHO1FBQ1IsT0FBTyxJQUFJLFVBQVUsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFDN0IsQ0FBQztJQU9EOztPQUVHO0lBQ0gsS0FBSztRQUNILE9BQU8sSUFBSSxVQUFVLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLEVBQUUsQ0FBQyxDQUFDO0lBQzdDLENBQUM7SUFFRDs7O09BR0c7SUFDSCxHQUFHLENBQUMsS0FBaUI7UUFDbkIsTUFBTSxNQUFNLEdBQUcsSUFBSSxDQUFDLEtBQUssRUFBRSxDQUFDO1FBQzVCLE1BQU0sQ0FBQyxTQUFTLENBQUMsS0FBSyxDQUFDLENBQUM7UUFDeEIsT0FBTyxNQUFNLENBQUM7SUFDaEIsQ0FBQztJQUVEOztPQUVHO0lBQ0gsU0FBUyxDQUFDLEtBQWlCO1FBQ3pCLE1BQU0sYUFBYSxHQUFHLElBQUksQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLEVBQUUsS0FBSyxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsQ0FBQztRQUN4RSxJQUFJLEtBQUssR0FBRyxDQUFDLENBQUM7UUFDZCxLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsYUFBYSxF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QWEsQ0FBQyxDQUFDO1NBQ3hEO1FBQ0QsT0FBTyxJQUFJLENBQUMsV0FBVyxDQUFDLFFBQVEsQ0FBQyxDQUFDO0lBQ3BDLENBQUM7Q0FDRjtBQUVEOzs7OztHQUtHO0FBQ0gsTUFBTSxPQUFPLG9CQUFvQjtJQUcvQixZQUE2QixJQUFZO1FBQVosU0FBSSxHQUFKLElBQUksQ0FBUTtRQUZ4QixjQUFTLEdBQUcsQ0FBQyxJQUFJLHVCQUF1QixDQUFDLFVBQVUsQ0FBQyxHQUFHLEVBQUUsQ0FBQyxDQUFDLENBQUM7SUFFakMsQ0FBQztJQUU3Qzs7O09BR0c7SUFDSCxZQUFZLENBQUMsUUFBZ0I7UUFDM0IsOEZBQThGO1FBQzlGLDhGQUE4RjtRQUM5RixpQ0FBaUM7UUFDakMsS0FBSyxJQUFJLENBQUMsR0FBRyxJQUFJLENBQUMsU0FBUyxDQUFDLE1BQU0sRUFBRSxDQUFDLElBQUksUUFBUSxFQUFFLENBQUMsRUFBRSxFQUFFO1lBQ3RELE1BQU0sS0FBSyxHQUFHLElBQUksQ0FBQyxTQUFTLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLFVBQVUsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUM7WUFDMUQsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsR0FBRyxJQUFJLHVCQUF1QixDQUFDLEtBQUssQ0FBQyxDQUFDO1NBQ3hEO1FBQ0QsT0FBTyxJQUFJLENBQUMsU0FBUyxDQUFDLFFBQVEsQ0FBQyxDQUFDO0lBQ2xDLENBQUM7Q0FDRiIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG4vKipcbiAqIFJlcHJlc2VudHMgYSBiaWcgaW50ZWdlciB1c2luZyBhIGJ1ZmZlciBvZiBpdHMgaW5kaXZpZHVhbCBkaWdpdHMsIHdpdGggdGhlIGxlYXN0IHNpZ25pZmljYW50XG4gKiBkaWdpdCBzdG9yZWQgYXQgdGhlIGJlZ2lubmluZyBvZiB0aGUgYXJyYXkgKGxpdHRsZSBlbmRpYW4pLlxuICpcbiAqIEZvciBwZXJmb3JtYW5jZSByZWFzb25zLCBlYWNoIGluc3RhbmNlIGlzIG11dGFibGUuIFRoZSBhZGRpdGlvbiBvcGVyYXRpb24gY2FuIGJlIGRvbmUgaW4tcGxhY2VcbiAqIHRvIHJlZHVjZSBtZW1vcnkgcHJlc3N1cmUgb2YgYWxsb2NhdGlvbiBmb3IgdGhlIGRpZ2l0cyBhcnJheS5cbiAqL1xuZXhwb3J0IGNsYXNzIEJpZ0ludGVnZXIge1xuICBzdGF0aWMgemVybygpOiBCaWdJbnRlZ2VyIHtcbiAgICByZXR1cm4gbmV3IEJpZ0ludGVnZXIoWzBdKTtcbiAgfVxuXG4gIHN0YXRpYyBvbmUoKTogQmlnSW50ZWdlciB7XG4gICAgcmV0dXJuIG5ldyBCaWdJbnRlZ2VyKFsxXSk7XG4gIH1cblxuICAvKipcbiAgICogQ3JlYXRlcyBhIGJpZyBpbnRlZ2VyIHVzaW5nIGl0cyBpbmRpdmlkdWFsIGRpZ2l0cyBpbiBsaXR0bGUgZW5kaWFuIHN0b3JhZ2UuXG4gICAqL1xuICBwcml2YXRlIGNvbnN0cnVjdG9yKHByaXZhdGUgcmVhZG9ubHkgZGlnaXRzOiBudW1iZXJbXSkge31cblxuICAvKipcbiAgICogQ3JlYXRlcyBhIGNsb25lIG9mIHRoaXMgaW5zdGFuY2UuXG4gICAqL1xuICBjbG9uZSgpOiBCaWdJbnRlZ2VyIHtcbiAgICByZXR1cm4gbmV3IEJpZ0ludGVnZXIodGhpcy5kaWdpdHMuc2xpY2UoKSk7XG4gIH1cblxuICAvKipcbiAgICogUmV0dXJucyBhIG5ldyBiaWcgaW50ZWdlciB3aXRoIHRoZSBzdW0gb2YgYHRoaXNgIGFuZCBgb3RoZXJgIGFzIGl0cyB2YWx1ZS4gVGhpcyBkb2VzIG5vdCBtdXRhdGVcbiAgICogYHRoaXNgIGJ1dCBpbnN0ZWFkIHJldHVybnMgYSBuZXcgaW5zdGFuY2UsIHVubGlrZSBgYWRkVG9TZWxmYC5cbiAgICovXG4gIGFkZChvdGhlcjogQmlnSW50ZWdlcik6IEJpZ0ludGVnZXIge1xuICAgIGNvbnN0IHJlc3VsdCA9IHRoaXMuY2xvbmUoKTtcbiAgICByZXN1bHQuYWRkVG9TZWxmKG90aGVyKTtcbiAgICByZXR1cm4gcmVzdWx0O1xuICB9XG5cbiAgLyoqXG4gICAqIEFkZHMgYG90aGVyYCB0byB0aGUgaW5zdGFuY2UgaXRzZWxmLCB0aGVyZWJ5IG11dGF0aW5nIGl0cyB2YWx1ZS5cbiAgICovXG4gIGFkZFRvU2VsZihvdGhlcjogQmlnSW50ZWdlcik6IHZvaWQge1xuICAgIGNvbnN0IG1heE5yT2ZEaWdpdHMgPSBNYXRoLm1heCh0aGlzLmRpZ2l0cy5sZW5ndGgsIG90aGVyLmRpZ2l0cy5sZW5ndGgpO1xuICAgIGxldCBjYXJyeSA9IDA7XG4gICAgZm9yIChsZXQgaSA9IDA7IGkgPCBtYXhOck9mRGlnaXRzOyBpKyspIHtcbiAgICAgIGxldCBkaWdpdFN1bSA9IGNhcnJ5O1xuICAgICAgaWYgKGkgPCB0aGlzLmRpZ2l0cy5sZW5ndGgpIHtcbiAgICAgICAgZGlnaXRTdW0gKz0gdGhpcy5kaWdpdHNbaV07XG4gICAgICB9XG4gICAgICBpZiAoaSA8IG90aGVyLmRpZ2l0cy5sZW5ndGgpIHtcbiAgICAgICAgZGlnaXRTdW0gKz0gb3RoZXIuZGlnaXRzW2ldO1xuICAgICAgfVxuXG4gICAgICBpZiAoZGlnaXRTdW0gPj0gMTApIHtcbiAgICAgICAgdGhpcy5kaWdpdHNbaV0gPSBkaWdpdFN1bSAtIDEwO1xuICAgICAgICBjYXJyeSA9IDE7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICB0aGlzLmRpZ2l0c1tpXSA9IGRpZ2l0U3VtO1xuICAgICAgICBjYXJyeSA9IDA7XG4gICAgICB9XG4gICAgfVxuXG4gICAgLy8gQXBwbHkgYSByZW1haW5pbmcgY2FycnkgaWYgbmVlZGVkLlxuICAgIGlmIChjYXJyeSA+IDApIHtcbiAgICAgIHRoaXMuZGlnaXRzW21heE5yT2ZEaWdpdHNdID0gMTtcbiAgICB9XG4gIH1cblxuICAvKipcbiAgICogQnVpbGRzIHRoZSBkZWNpbWFsIHN0cmluZyByZXByZXNlbnRhdGlvbiBvZiB0aGUgYmlnIGludGVnZXIuIEFzIHRoaXMgaXMgc3RvcmVkIGluXG4gICAqIGxpdHRsZSBlbmRpYW4sIHRoZSBkaWdpdHMgYXJlIGNvbmNhdGVuYXRlZCBpbiByZXZlcnNlIG9yZGVyLlxuICAgKi9cbiAgdG9TdHJpbmcoKTogc3RyaW5nIHtcbiAgICBsZXQgcmVzID0gJyc7XG4gICAgZm9yIChsZXQgaSA9IHRoaXMuZGlnaXRzLmxlbmd0aCAtIDE7IGkgPj0gMDsgaS0tKSB7XG4gICAgICByZXMgKz0gdGhpcy5kaWdpdHNbaV07XG4gICAgfVxuICAgIHJldHVybiByZXM7XG4gIH1cbn1cblxuLyoqXG4gKiBSZXByZXNlbnRzIGEgYmlnIGludGVnZXIgd2hpY2ggaXMgb3B0aW1pemVkIGZvciBtdWx0aXBsaWNhdGlvbiBvcGVyYXRpb25zLCBhcyBpdHMgcG93ZXItb2YtdHdvc1xuICogYXJlIG1lbW9pemVkLiBTZWUgYG11bHRpcGx5QnkoKWAgZm9yIGRldGFpbHMgb24gdGhlIG11bHRpcGxpY2F0aW9uIGFsZ29yaXRobS5cbiAqL1xuZXhwb3J0IGNsYXNzIEJpZ0ludEZvck11bHRpcGxpY2F0aW9uIHtcbiAgLyoqXG4gICAqIFN0b3JlcyBhbGwgbWVtb2l6ZWQgcG93ZXItb2YtdHdvcywgd2hlcmUgZWFjaCBpbmRleCByZXByZXNlbnRzIGB0aGlzLm51bWJlciAqIDJeaW5kZXhgLlxuICAgKi9cbiAgcHJpdmF0ZSByZWFkb25seSBwb3dlck9mVHdvczogQmlnSW50ZWdlcltdO1xuXG4gIGNvbnN0cnVjdG9yKHZhbHVlOiBCaWdJbnRlZ2VyKSB7XG4gICAgdGhpcy5wb3dlck9mVHdvcyA9IFt2YWx1ZV07XG4gIH1cblxuICAvKipcbiAgICogUmV0dXJucyB0aGUgYmlnIGludGVnZXIgaXRzZWxmLlxuICAgKi9cbiAgZ2V0VmFsdWUoKTogQmlnSW50ZWdlciB7XG4gICAgcmV0dXJuIHRoaXMucG93ZXJPZlR3b3NbMF07XG4gIH1cblxuICAvKipcbiAgICogQ29tcHV0ZXMgdGhlIHZhbHVlIGZvciBgbnVtICogYmAsIHdoZXJlIGBudW1gIGlzIGEgSlMgbnVtYmVyIGFuZCBgYmAgaXMgYSBiaWcgaW50ZWdlci4gVGhlXG4gICAqIHZhbHVlIGZvciBgYmAgaXMgcmVwcmVzZW50ZWQgYnkgYSBzdG9yYWdlIG1vZGVsIHRoYXQgaXMgb3B0aW1pemVkIGZvciB0aGlzIGNvbXB1dGF0aW9uLlxuICAgKlxuICAgKiBUaGlzIG9wZXJhdGlvbiBpcyBpbXBsZW1lbnRlZCBpbiBOKGxvZzIobnVtKSkgYnkgY29udGludW91cyBoYWx2aW5nIG9mIHRoZSBudW1iZXIsIHdoZXJlIHRoZVxuICAgKiBsZWFzdC1zaWduaWZpY2FudCBiaXQgKExTQikgaXMgdGVzdGVkIGluIGVhY2ggaXRlcmF0aW9uLiBJZiB0aGUgYml0IGlzIHNldCwgdGhlIGJpdCdzIGluZGV4IGlzXG4gICAqIHVzZWQgYXMgZXhwb25lbnQgaW50byB0aGUgcG93ZXItb2YtdHdvIG11bHRpcGxpY2F0aW9uIG9mIGBiYC5cbiAgICpcbiAgICogQXMgYW4gZXhhbXBsZSwgY29uc2lkZXIgdGhlIG11bHRpcGxpY2F0aW9uIG51bT00MiwgYj0xMzM3LiBJbiBiaW5hcnkgNDIgaXMgMGIwMDEwMTAxMCBhbmQgdGhlXG4gICAqIGFsZ29yaXRobSB1bnJvbGxzIGludG8gdGhlIGZvbGxvd2luZyBpdGVyYXRpb25zOlxuICAgKlxuICAgKiAgSXRlcmF0aW9uIHwgbnVtICAgICAgICB8IExTQiAgfCBiICogMl5pdGVyIHwgQWRkPyB8IHByb2R1Y3RcbiAgICogLS0tLS0tLS0tLS18LS0tLS0tLS0tLS0tfC0tLS0tLXwtLS0tLS0tLS0tLS18LS0tLS0tfC0tLS0tLS0tXG4gICAqICAwICAgICAgICAgfCAwYjAwMTAxMDEwIHwgMCAgICB8IDEzMzcgICAgICAgfCBObyAgIHwgMFxuICAgKiAgMSAgICAgICAgIHwgMGIwMDAxMDEwMSB8IDEgICAgfCAyNjc0ICAgICAgIHwgWWVzICB8IDI2NzRcbiAgICogIDIgICAgICAgICB8IDBiMDAwMDEwMTAgfCAwICAgIHwgNTM0OCAgICAgICB8IE5vICAgfCAyNjc0XG4gICAqICAzICAgICAgICAgfCAwYjAwMDAwMTAxIHwgMSAgICB8IDEwNjk2ICAgICAgfCBZZXMgIHwgMTMzNzBcbiAgICogIDQgICAgICAgICB8IDBiMDAwMDAwMTAgfCAwICAgIHwgMjEzOTIgICAgICB8IE5vICAgfCAxMzM3MFxuICAgKiAgNSAgICAgICAgIHwgMGIwMDAwMDAwMSB8IDEgICAgfCA0Mjc4NCAgICAgIHwgWWVzICB8IDU2MTU0XG4gICAqICA2ICAgICAgICAgfCAwYjAwMDAwMDAwIHwgMCAgICB8IDg1NTY4ICAgICAgfCBObyAgIHwgNTYxNTRcbiAgICpcbiAgICogVGhlIGNvbXB1dGVkIHByb2R1Y3Qgb2YgNTYxNTQgaXMgaW5kZWVkIHRoZSBjb3JyZWN0IHJlc3VsdC5cbiAgICpcbiAgICogVGhlIGBCaWdJbnRGb3JNdWx0aXBsaWNhdGlvbmAgcmVwcmVzZW50YXRpb24gZm9yIGEgYmlnIGludGVnZXIgcHJvdmlkZXMgbWVtb2l6ZWQgYWNjZXNzIHRvIHRoZVxuICAgKiBwb3dlci1vZi10d28gdmFsdWVzIHRvIHJlZHVjZSB0aGUgd29ya2xvYWQgaW4gY29tcHV0aW5nIHRob3NlIHZhbHVlcy5cbiAgICovXG4gIG11bHRpcGx5QnkobnVtOiBudW1iZXIpOiBCaWdJbnRlZ2VyIHtcbiAgICBjb25zdCBwcm9kdWN0ID0gQmlnSW50ZWdlci56ZXJvKCk7XG4gICAgdGhpcy5tdWx0aXBseUJ5QW5kQWRkVG8obnVtLCBwcm9kdWN0KTtcbiAgICByZXR1cm4gcHJvZHVjdDtcbiAgfVxuXG4gIC8qKlxuICAgKiBTZWUgYG11bHRpcGx5QnkoKWAgZm9yIGRldGFpbHMuIFRoaXMgZnVuY3Rpb24gYWxsb3dzIGZvciB0aGUgY29tcHV0ZWQgcHJvZHVjdCB0byBiZSBhZGRlZFxuICAgKiBkaXJlY3RseSB0byB0aGUgcHJvdmlkZWQgcmVzdWx0IGJpZyBpbnRlZ2VyLlxuICAgKi9cbiAgbXVsdGlwbHlCeUFuZEFkZFRvKG51bTogbnVtYmVyLCByZXN1bHQ6IEJpZ0ludGVnZXIpOiB2b2lkIHtcbiAgICBmb3IgKGxldCBleHBvbmVudCA9IDA7IG51bSAhPT0gMDsgbnVtID0gbnVtID4+PiAxLCBleHBvbmVudCsrKSB7XG4gICAgICBpZiAobnVtICYgMSkge1xuICAgICAgICBjb25zdCB2YWx1ZSA9IHRoaXMuZ2V0TXVsdGlwbGllZEJ5UG93ZXJPZlR3byhleHBvbmVudCk7XG4gICAgICAgIHJlc3VsdC5hZGRUb1NlbGYodmFsdWUpO1xuICAgICAgfVxuICAgIH1cbiAgfVxuXG4gIC8qKlxuICAgKiBDb21wdXRlcyBhbmQgbWVtb2l6ZXMgdGhlIGJpZyBpbnRlZ2VyIHZhbHVlIGZvciBgdGhpcy5udW1iZXIgKiAyXmV4cG9uZW50YC5cbiAgICovXG4gIHByaXZhdGUgZ2V0TXVsdGlwbGllZEJ5UG93ZXJPZlR3byhleHBvbmVudDogbnVtYmVyKTogQmlnSW50ZWdlciB7XG4gICAgLy8gQ29tcHV0ZSB0aGUgcG93ZXJzIHVwIHVudGlsIHRoZSByZXF1ZXN0ZWQgZXhwb25lbnQsIHdoZXJlIGVhY2ggdmFsdWUgaXMgY29tcHV0ZWQgZnJvbSBpdHNcbiAgICAvLyBwcmVkZWNlc3Nvci4gVGhpcyBpcyBzaW1wbGUgYXMgYHRoaXMubnVtYmVyICogMl4oZXhwb25lbnQgLSAxKWAgb25seSBoYXMgdG8gYmUgZG91YmxlZCAoaS5lLlxuICAgIC8vIGFkZGVkIHRvIGl0c2VsZikgdG8gcmVhY2ggYHRoaXMubnVtYmVyICogMl5leHBvbmVudGAuXG4gICAgZm9yIChsZXQgaSA9IHRoaXMucG93ZXJPZlR3b3MubGVuZ3RoOyBpIDw9IGV4cG9uZW50OyBpKyspIHtcbiAgICAgIGNvbnN0IHByZXZpb3VzUG93ZXIgPSB0aGlzLnBvd2VyT2ZUd29zW2kgLSAxXTtcbiAgICAgIHRoaXMucG93ZXJPZlR3b3NbaV0gPSBwcmV2aW91c1Bvd2VyLmFkZChwcmV2aW91c1Bvd2VyKTtcbiAgICB9XG4gICAgcmV0dXJuIHRoaXMucG93ZXJPZlR3b3NbZXhwb25lbnRdO1xuICB9XG59XG5cbi8qKlxuICogUmVwcmVzZW50cyBhbiBleHBvbmVudGlhdGlvbiBvcGVyYXRpb24gZm9yIHRoZSBwcm92aWRlZCBiYXNlLCBvZiB3aGljaCBleHBvbmVudHMgYXJlIGNvbXB1dGVkIGFuZFxuICogbWVtb2l6ZWQuIFRoZSByZXN1bHRzIGFyZSByZXByZXNlbnRlZCBieSBhIGBCaWdJbnRGb3JNdWx0aXBsaWNhdGlvbmAgd2hpY2ggaXMgdGFpbG9yZWQgZm9yXG4gKiBtdWx0aXBsaWNhdGlvbiBvcGVyYXRpb25zIGJ5IG1lbW9pemluZyB0aGUgcG93ZXItb2YtdHdvcy4gVGhpcyBlZmZlY3RpdmVseSByZXN1bHRzIGluIGEgbWF0cml4XG4gKiByZXByZXNlbnRhdGlvbiB0aGF0IGlzIGxhemlseSBjb21wdXRlZCB1cG9uIHJlcXVlc3QuXG4gKi9cbmV4cG9ydCBjbGFzcyBCaWdJbnRFeHBvbmVudGlhdGlvbiB7XG4gIHByaXZhdGUgcmVhZG9ubHkgZXhwb25lbnRzID0gW25ldyBCaWdJbnRGb3JNdWx0aXBsaWNhdGlvbihCaWdJbnRlZ2VyLm9uZSgpKV07XG5cbiAgY29uc3RydWN0b3IocHJpdmF0ZSByZWFkb25seSBiYXNlOiBudW1iZXIpIHt9XG5cbiAgLyoqXG4gICAqIENvbXB1dGUgdGhlIHZhbHVlIGZvciBgdGhpcy5iYXNlXmV4cG9uZW50YCwgcmVzdWx0aW5nIGluIGEgYmlnIGludGVnZXIgdGhhdCBpcyBvcHRpbWl6ZWQgZm9yXG4gICAqIGZ1cnRoZXIgbXVsdGlwbGljYXRpb24gb3BlcmF0aW9ucy5cbiAgICovXG4gIHRvVGhlUG93ZXJPZihleHBvbmVudDogbnVtYmVyKTogQmlnSW50Rm9yTXVsdGlwbGljYXRpb24ge1xuICAgIC8vIENvbXB1dGUgdGhlIHJlc3VsdHMgdXAgdW50aWwgdGhlIHJlcXVlc3RlZCBleHBvbmVudCwgd2hlcmUgZXZlcnkgdmFsdWUgaXMgY29tcHV0ZWQgZnJvbSBpdHNcbiAgICAvLyBwcmVkZWNlc3Nvci4gVGhpcyBpcyBiZWNhdXNlIGB0aGlzLmJhc2VeKGV4cG9uZW50IC0gMSlgIG9ubHkgaGFzIHRvIGJlIG11bHRpcGxpZWQgYnkgYGJhc2VgXG4gICAgLy8gdG8gcmVhY2ggYHRoaXMuYmFzZV5leHBvbmVudGAuXG4gICAgZm9yIChsZXQgaSA9IHRoaXMuZXhwb25lbnRzLmxlbmd0aDsgaSA8PSBleHBvbmVudDsgaSsrKSB7XG4gICAgICBjb25zdCB2YWx1ZSA9IHRoaXMuZXhwb25lbnRzW2kgLSAxXS5tdWx0aXBseUJ5KHRoaXMuYmFzZSk7XG4gICAgICB0aGlzLmV4cG9uZW50c1tpXSA9IG5ldyBCaWdJbnRGb3JNdWx0aXBsaWNhdGlvbih2YWx1ZSk7XG4gICAgfVxuICAgIHJldHVybiB0aGlzLmV4cG9uZW50c1tleHBvbmVudF07XG4gIH1cbn1cbiJdfQ==
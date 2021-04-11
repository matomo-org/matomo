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
export declare class BigInteger {
    private readonly digits;
    static zero(): BigInteger;
    static one(): BigInteger;
    /**
     * Creates a big integer using its individual digits in little endian storage.
     */
    private constructor();
    /**
     * Creates a clone of this instance.
     */
    clone(): BigInteger;
    /**
     * Returns a new big integer with the sum of `this` and `other` as its value. This does not mutate
     * `this` but instead returns a new instance, unlike `addToSelf`.
     */
    add(other: BigInteger): BigInteger;
    /**
     * Adds `other` to the instance itself, thereby mutating its value.
     */
    addToSelf(other: BigInteger): void;
    /**
     * Builds the decimal string representation of the big integer. As this is stored in
     * little endian, the digits are concatenated in reverse order.
     */
    toString(): string;
}
/**
 * Represents a big integer which is optimized for multiplication operations, as its power-of-twos
 * are memoized. See `multiplyBy()` for details on the multiplication algorithm.
 */
export declare class BigIntForMultiplication {
    /**
     * Stores all memoized power-of-twos, where each index represents `this.number * 2^index`.
     */
    private readonly powerOfTwos;
    constructor(value: BigInteger);
    /**
     * Returns the big integer itself.
     */
    getValue(): BigInteger;
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
    multiplyBy(num: number): BigInteger;
    /**
     * See `multiplyBy()` for details. This function allows for the computed product to be added
     * directly to the provided result big integer.
     */
    multiplyByAndAddTo(num: number, result: BigInteger): void;
    /**
     * Computes and memoizes the big integer value for `this.number * 2^exponent`.
     */
    private getMultipliedByPowerOfTwo;
}
/**
 * Represents an exponentiation operation for the provided base, of which exponents are computed and
 * memoized. The results are represented by a `BigIntForMultiplication` which is tailored for
 * multiplication operations by memoizing the power-of-twos. This effectively results in a matrix
 * representation that is lazily computed upon request.
 */
export declare class BigIntExponentiation {
    private readonly base;
    private readonly exponents;
    constructor(base: number);
    /**
     * Compute the value for `this.base^exponent`, resulting in a big integer that is optimized for
     * further multiplication operations.
     */
    toThePowerOf(exponent: number): BigIntForMultiplication;
}

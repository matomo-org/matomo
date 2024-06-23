/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

/* eslint-disable @typescript-eslint/ban-ts-comment */

import {
  create as createMathJs,
  evaluateDependencies,
  // @ts-ignore
} from 'mathjs/lib/esm/number';

const math = createMathJs({
  evaluateDependencies,
});

// add our own simple operators to avoid having to import math.js' ones, to keep the
// generated size down.
math.import(
  {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    add: (a: any, b: any) => a + b,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    subtract: (a: any, b: any) => a - b,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    multiply: (a: any, b: any) => a * b,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    divide: (a: any, b: any) => a / b,
    // eslint-disable-next-line
    equal: (a: any, b: any) => a == b,
    // eslint-disable-next-line
    unequal: (a: any, b: any) => a != b,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    not: (a: any) => !a,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    and: (a: any, b: any) => a && b,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    or: (a: any, b: any) => a || b,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    largerEq: (a: any, b: any) => a >= b,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    larger: (a: any, b: any) => a > b,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    smallerEq: (a: any, b: any) => a <= b,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    smaller: (a: any, b: any) => a < b,
  },
  {
    override: true,
  },
);

export default math;

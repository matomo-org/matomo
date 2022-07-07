/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

import {
  create as createMathJs,
  addDependencies,
  subtractDependencies,
  multiplyDependencies,
  divideDependencies,
  equalDependencies,
  notDependencies,
  andDependencies,
  orDependencies,
  evaluateDependencies,
  largerDependencies,
  largerEqDependencies,
  smallerEqDependencies,
  smallerDependencies,
  unequalDependencies,
} from 'mathjs';

const math = createMathJs({
  addDependencies,
  subtractDependencies,
  multiplyDependencies,
  divideDependencies,
  equalDependencies,
  notDependencies,
  andDependencies,
  orDependencies,
  evaluateDependencies,
  largerDependencies,
  largerEqDependencies,
  smallerEqDependencies,
  smallerDependencies,
  unequalDependencies,
});

// support natural equal for strings (or any variable)
math.import(
  {
    // eslint-disable-next-line eqeqeq
    equal: (a: unknown, b: unknown) => a == b,
    // eslint-disable-next-line eqeqeq
    unequal: (a: unknown, b: unknown) => a != b,
  },
  {
    override: true,
  },
);

export default math;

/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const { setupFixture } = require('./matomo');

// All ported specs use the default UI fixture, so it is set up once per run. A spec that needs a
// different fixture calls setupFixture() in its own beforeAll.
module.exports = async () => {
  setupFixture();
};
